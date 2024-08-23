FROM acr-ext.is.adyen.com/external/nginx:1.24.0

LABEL maintainer="Ecommerce Plugins"

WORKDIR /var/www/html

ARG PHP_VERSION

ENV MAGENTO_VERSION="<will be defined>" \
    MAGENTO_HOST="<will be defined>" \
    VIRTUAL_HOST="<will be defined>" \
    DB_SERVER="<will be defined>" \
    DB_PORT=3306 \
    DB_NAME="<will be defined>" \
    DB_USER="<will be defined>" \
    DB_PASSWORD="<will be defined>" \
    DB_PREFIX=m2_ \
    ELASTICSEARCH_SERVER="<will be defined>" \
    ELASTICSEARCH_PORT=9200 \
    ELASTICSEARCH_INDEX_PREFIX=magento2 \
    ELASTICSEARCH_TIMEOUT=15 \
    ADMIN_NAME=admin \
    ADMIN_LASTNAME=admin \
    ADMIN_EMAIL=admin@example.com \
    ADMIN_USERNAME=admin \
    ADMIN_PASSWORD=admin123 \
    ADMIN_URLEXT=admin \
    MAGENTO_LANGUAGE=en_US \
    MAGENTO_CURRENCY=EUR \
    MAGENTO_TZ=Europe/Amsterdam \
    DEPLOY_SAMPLEDATA=0 \
    PHP_IDE_CONFIG=serverName=localhost \
    USE_SSL=0 \
    PHP_VERSION=${PHP_VERSION} \
    MAGE_ROOT=/var/www/html

RUN apt-get update -y --fix-missing \
    && apt install -y apt-transport-https lsb-release ca-certificates wget \
    && wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg \
    && echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/php.list

RUN apt-get update -y --fix-missing \
    && apt-get install -y \
        php${PHP_VERSION}-bcmath \
        php${PHP_VERSION}-common \
        php${PHP_VERSION}-curl \
        php${PHP_VERSION}-fpm \
        php${PHP_VERSION}-gd \
        php${PHP_VERSION}-intl \
        php${PHP_VERSION}-mbstring \
        php${PHP_VERSION}-mysql \
        php${PHP_VERSION}-soap \
        php${PHP_VERSION}-xml \
        php${PHP_VERSION}-xsl\
        php${PHP_VERSION}-zip \
        php${PHP_VERSION}-xdebug \
        default-mysql-client \
        git \
        nano \
        vim \
        ssh \
	cron \
    && apt upgrade -y -y

RUN mkdir -p /root/.ssh && chmod 700 /root/.ssh

#COPY src/config/90-custom-php.ini /etc/php/${PHP_VERSION}/fpm/conf.d/90-custom-php.ini
#COPY src/config/20-xdebug.ini /etc/php/${PHP_VERSION}/fpm/conf.d/20-xdebug.ini
#COPY src/certificates/magento.cer /etc/nginx/magento.cer
#COPY src/certificates/magento.key /etc/nginx/magento.key
#COPY src/script/install_magento.sh /tmp/install_magento.sh

COPY config/php.ini /usr/local/etc/php/
COPY scripts/install_magento.sh /tmp/install_magento.sh

ARG SSH_PRIVATE_KEY
#RUN mkdir -p /root/.ssh
RUN echo "${SSH_PRIVATE_KEY}" > /root/.ssh/hyva_id_rsa
RUN chmod 600 ~/.ssh/hyva_id_rsa
RUN ssh-keyscan gitlab.hyva.io >> /root/.ssh/known_hosts

RUN echo "Host gitlab.hyva.io" >> /root/.ssh/config
RUN echo "  StrictHostKeyChecking no" >> /root/.ssh/config
RUN echo "  IdentityFile /root/.ssh/hyva_id_rsa" >> /root/.ssh/config
RUN chmod 600 /root/.ssh/config

RUN chmod +x /tmp/install_magento.sh

CMD ["bash", "/tmp/install_magento.sh"]
