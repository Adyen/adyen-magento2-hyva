#!/bin/bash

#if [[ -e /etc/php/${PHP_VERSION}/cli/conf.d/20-xdebug.ini ]]; then
#	# Disable Xdebug for CLI
#	mv /etc/php/${PHP_VERSION}/cli/conf.d/20-xdebug.ini /etc/php/${PHP_VERSION}/cli/conf.d/20-xdebug.ini.disabled
#	service php${PHP_VERSION}-fpm restart
#	service nginx restart
#fi

if [ "${MAGENTO_VERSION}" == "<will be defined>" ]; then
	echo "MAGENTO_VERSION is not defined!"
	exit 1
fi

if [[ -e /usr/local/bin/composer ]]; then
	echo "Composer already exists"
else
	php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
	php composer-setup.php --quiet
	rm composer-setup.php
	mv composer.phar /usr/local/bin/composer
fi

if [ "${DB_SERVER}" != "<will be defined>" ]; then
	RET=1
	while [ $RET -ne 0 ]; do
		echo "Checking if ${DB_SERVER} is available."
		mysql -h "${DB_SERVER}" -P "${DB_PORT}" -u "${DB_USER}" -p"${DB_PASSWORD}" -e "status" >/dev/null 2>&1
		RET=$?

		if [ $RET -ne 0 ]; then
			echo "Connection to MySQL/MariaDB is pending."
			sleep 5
		fi
	done
	echo "DB server ${DB_SERVER} is available."
else
	echo "MySQL/MariaDB server is not defined!"
	exit 1
fi

MAGENTO_INSTALL_ARGS="";
USE_ELASTICSEARCH='1'
if [ "$USE_ELASTICSEARCH" == '1' ] && [ "${ELASTICSEARCH_SERVER}" != "<will be defined>" ]; then
	MAGENTO_INSTALL_ARGS=$(echo \
		--elasticsearch-host="${ELASTICSEARCH_SERVER}" \
		--elasticsearch-port="${ELASTICSEARCH_PORT}" \
		--elasticsearch-index-prefix="${ELASTICSEARCH_INDEX_PREFIX}" \
		--elasticsearch-timeout="${ELASTICSEARCH_TIMEOUT}")
	RET=1
	while [ $RET -ne 0 ]; do
		echo "Checking if ${ELASTICSEARCH_SERVER} is available."
		curl -XGET "${ELASTICSEARCH_SERVER}:${ELASTICSEARCH_PORT}/_cat/health?v&pretty" >/dev/null 2>&1
		RET=$?

		if [ $RET -ne 0 ]; then
			echo "Connection to Elasticsearch is pending."
			sleep 5
		fi
	done
	echo "Elasticsearch server ${ELASTICSEARCH_SERVER} is available."
fi

# Change working directory to web server root
cd /var/www/html

if [[ -e /var/www/html/composer.lock ]]; then
	echo "Magento 2 is already installed."
else
	# Configure Nginx
	tee -a /etc/nginx/conf.d/magento.conf <<EOF
upstream fastcgi_backend {
  server  unix:/run/php/php${PHP_VERSION}-fpm.sock;
}

server {
  listen 8080;
  listen 8443 ssl;
  server_name ${VIRTUAL_HOST};
  ssl_certificate magento.cer;
  ssl_certificate_key magento.key;
  set \$MAGE_ROOT /var/www/html;
  include /var/www/html/nginx.conf.sample;
}

EOF

	mkdir /run/php
	update-alternatives --set php /usr/bin/php${PHP_VERSION}
	usermod -a -G www-data nginx
	service php${PHP_VERSION}-fpm restart
	service nginx restart

	# Install Magento
	wget https://github.com/magento/magento2/archive/refs/tags/${MAGENTO_VERSION}.tar.gz
	wget -O ../sample-data.tar.gz https://github.com/magento/magento2-sample-data/archive/refs/tags/${MAGENTO_VERSION}.tar.gz

	tar -xf ${MAGENTO_VERSION}.tar.gz --strip-components 1
	rm ${MAGENTO_VERSION}.tar.gz

	composer install -n

	find var generated vendor pub/static pub/media app/etc -type f -exec chmod g+w {} +
	find var generated vendor pub/static pub/media app/etc -type d -exec chmod g+ws {} +
	chown -R www-data:www-data .
	chmod u+x bin/magento

	bin/magento setup:install \
		--base-url="http://${MAGENTO_HOST}" \
		--db-host="${DB_SERVER}:${DB_PORT}" \
		--db-name="${DB_NAME}" \
		--db-user="${DB_USER}" \
		--db-password="${DB_PASSWORD}" \
		--admin-firstname="${ADMIN_NAME}" \
		--admin-lastname="${ADMIN_LASTNAME}" \
		--admin-email="${ADMIN_EMAIL}" \
		--admin-user="${ADMIN_USERNAME}" \
		--admin-password="${ADMIN_PASSWORD}" \
		--backend-frontname="${ADMIN_URLEXT}" \
		--language=en_US \
		--currency=EUR \
		--timezone=Europe/Amsterdam \
		--use-rewrites=1 \
		--cleanup-database \
		$MAGENTO_INSTALL_ARGS;

	bin/magento setup:di:compile
	bin/magento setup:static-content:deploy -f
	bin/magento indexer:reindex
	bin/magento deploy:mode:set developer
	bin/magento maintenance:disable
	bin/magento cron:install

	echo "Magento installation completed"

	# Install sample data
	echo "Installing sample data"
	mkdir ../sample-data
	tar -xf ../sample-data.tar.gz --strip-components 1 -C ../sample-data
	rm ../sample-data.tar.gz
	php -f ../sample-data/dev/tools/build-sample-data.php -- --ce-source="/var/www/html"
	bin/magento setup:upgrade

  # Set up SSH config for gitlab.hyva.io
  echo "Host gitlab.hyva.io" >> /root/.ssh/config && \
  echo "  StrictHostKeyChecking no" >> /root/.ssh/config && \
  echo "  IdentityFile /root/.ssh/hyva_id_rsa" >> /root/.ssh/config && \
  chmod 600 /root/.ssh/config

  # Configure composer
  echo "Configuring Composer with additional repositories"
  composer config repositories.hyva-themes/hyva-checkout git git@gitlab.hyva.io:hyva-checkout/checkout.git
  composer config repositories.hyva-themes/magento2-theme-module git git@gitlab.hyva.io:hyva-themes/magento2-theme-module.git
  composer config repositories.hyva-themes/magento2-reset-theme git git@gitlab.hyva.io:hyva-themes/magento2-reset-theme.git
  composer config repositories.hyva-themes/magento2-email-theme git git@gitlab.hyva.io:hyva-themes/magento2-email-module.git
  composer config repositories.hyva-default-theme git git@gitlab.hyva.io:hyva-themes/magento2-default-theme.git

  # Install Hyva compatibility module
  echo "Installing Hyva compatibility module"
  composer require adyen/module-hyva-checkout:dev-develop

  bin/magento module:enable --all
  bin/magento setup:di:compile
	bin/magento setup:static-content:deploy -f
	bin/magento cache:clean
fi

ISSET_USE_SSL=$(bin/magento config:show web/secure/use_in_frontend)
if [ "${USE_SSL}" -eq 1 ]; then
	if [ "${ISSET_USE_SSL:-0}" -eq 1 ]; then
		echo "Use SSL is set, but SSL is already enabled."
	else
		bin/magento setup:store-config:set \
			--base-url-secure="https://${MAGENTO_HOST}" \
			--use-secure=1 \
			--use-secure-admin=1
		echo "SSL for Magento is configured."
	fi
else
	echo "Use SSL is not set, skipping."
fi

service php${PHP_VERSION}-fpm restart
service nginx restart

service cron start

tail -f /dev/null
