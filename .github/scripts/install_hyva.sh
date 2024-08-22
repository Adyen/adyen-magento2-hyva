#!/bin/bash

MAGENTO_INSTALL_ARGS="";

if [ "$DB_SERVER" != "<will be defined>" ]; then
  RET=1
  while [ $RET -ne 0 ]; do
    echo "Checking if $DB_SERVER is available."
    mysql -h "$DB_SERVER" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASSWORD" -e "status" >/dev/null 2>&1
    RET=$?

    if [ $RET -ne 0 ]; then
      echo "Connection to MySQL/MariaDB is pending."
      sleep 5
    fi
  done
  echo "DB server $DB_SERVER is available."
else
  echo "MySQL/MariaDB server is not defined!"
  exit 1
fi

USE_ELASTICSEARCH='1'
if [[ "$MAGENTO_VERSION" =~ ^2\.3 ]]; then
  USE_ELASTICSEARCH='0'
fi

if [ "$USE_ELASTICSEARCH" == '1' ] && [ "$ELASTICSEARCH_SERVER" != "<will be defined>" ]; then
  MAGENTO_INSTALL_ARGS=$(echo \
    --elasticsearch-host="$ELASTICSEARCH_SERVER" \
    --elasticsearch-port="$ELASTICSEARCH_PORT" \
    --elasticsearch-index-prefix="$ELASTICSEARCH_INDEX_PREFIX" \
    --elasticsearch-timeout="$ELASTICSEARCH_TIMEOUT")
  RET=1
  while [ $RET -ne 0 ]; do
    echo "Checking if $ELASTICSEARCH_SERVER is available."
    curl -XGET "$ELASTICSEARCH_SERVER:$ELASTICSEARCH_PORT/_cat/health?v&pretty" >/dev/null 2>&1
    RET=$?

    if [ $RET -ne 0 ]; then
      echo "Connection to Elasticsearch is pending."
      sleep 5
    fi
  done
  echo "Elasticsearch server $ELASTICSEARCH_SERVER is available."
fi

if [[ -e /tmp/magento.tar.gz ]]; then
  mv /tmp/magento.tar.gz /var/www/html
else
  echo "Magento 2 tar is already moved to /var/www/html"
fi

if [[ -e /tmp/sample-data.tar.gz ]]; then
  mv /tmp/sample-data.tar.gz /var/www
else
  echo "Magento 2 sample data tar is alread moved to /var/www"
fi

if [[ -e /var/www/html/pub/index.php ]]; then
  echo "Already extracted Magento"
else
  tar -xf magento.tar.gz --strip-components 1
  rm magento.tar.gz
fi

if [[ -e /usr/local/bin/composer ]]; then
  echo "Composer already exists"
else
  php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
  php composer-setup.php --quiet
  rm composer-setup.php
  mv composer.phar /usr/local/bin/composer
fi
if [[ -d /var/www/html/vendor/magento ]]; then
	echo "Magento is already installed."
else
	composer install -n

	find var generated vendor pub/static pub/media app/etc -type f -exec chmod g+w {} +
	find var generated vendor pub/static pub/media app/etc -type d -exec chmod g+ws {} +
	chown -R www-data:www-data .
	chmod u+x bin/magento

	bin/magento setup:install \
		--base-url="http://$MAGENTO_HOST" \
		--db-host="$DB_SERVER:$DB_PORT" \
		--db-name="$DB_NAME" \
		--db-user="$DB_USER" \
		--db-password="$DB_PASSWORD" \
		--db-prefix="$DB_PREFIX" \
		--admin-firstname="$ADMIN_NAME" \
		--admin-lastname="$ADMIN_LASTNAME" \
		--admin-email="$ADMIN_EMAIL" \
		--admin-user="$ADMIN_USERNAME" \
		--admin-password="$ADMIN_PASSWORD" \
		--backend-frontname="$ADMIN_URLEXT" \
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

	echo "Installation completed"

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