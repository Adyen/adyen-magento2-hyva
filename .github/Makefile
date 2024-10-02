# Install N98-Magerun
n98-magerun2.phar:
	wget -q https://files.magerun.net/n98-magerun2.phar
	chmod +x ./n98-magerun2.phar

# Check Magento installation
sys-check: n98-magerun2.phar
	./n98-magerun2.phar sys:check

# Install Magento (without starting Apache)
magento:
	sed '/exec /d' /tmp/install_magento.sh | bash

hyva: install-hyva configure flush

# Hyva install
install-hyva:
	sed '/exec /d' /tmp/install_hyva.sh | bash

# Install developer module
dev-module:
	bin/magento module:enable Adyen_Developer
	bin/magento setup:upgrade

# Configuration
configure: n98-magerun2.phar
	echo "configuration step started"
	bin/magento config:set payment/adyen_abstract/demo_mode 1
	bin/magento adyen:enablepaymentmethods:run
	bin/magento config:set payment/adyen_abstract/has_holder_name 1
	bin/magento config:set payment/adyen_pay_by_link/active 1
	bin/magento config:set payment/adyen_pay_by_link/days_to_expire 5
	bin/magento config:set payment/adyen_giving/active 1
	bin/magento config:set payment/adyen_giving/charity_description 'test'
	bin/magento config:set payment/adyen_giving/charity_website 'https://adyen.com'
	bin/magento config:set payment/adyen_giving/charity_merchant_account "${DONATION_ACCOUNT}"
	bin/magento config:set payment/adyen_giving/donation_amounts '1,5,10'
	bin/magento config:set payment/adyen_giving/background_image ''
	bin/magento config:set payment/adyen_abstract/merchant_account "${ADYEN_MERCHANT}"
	bin/magento config:set payment/adyen_abstract/notifications_ip_check 0
	bin/magento config:set payment/adyen_abstract/payment_authorized 'processing'
	bin/magento config:set payment/adyen_abstract/payment_pre_authorized 'pending_payment'
	bin/magento config:set payment/adyen_abstract/capture_mode 'manual'
	bin/magento config:set payment/adyen_abstract/paypal_capture_mode 0
	bin/magento config:set payment/adyen_abstract/recurring_configuration '{"adyen_cc":{"name":"Credit Card","enabled":"1","recurringProcessingModel":"CardOnFile"}}'
	bin/magento config:set payment/adyen_cc_vault/require_cvc 1
	bin/magento config:set payment/adyen_abstract/client_key_test "${ADYEN_CLIENT_KEY}"
	bin/magento config:set payment/adyen_abstract/notification_username 'admin'
	bin/magento config:set payment/adyen_abstract/payment_methods_active 1
	bin/magento config:set payment/adyen_abstract/has_holder_name 1
	./n98-magerun2.phar config:store:set --encrypt payment/adyen_abstract/notification_password '1234'  > /dev/null
	./n98-magerun2.phar config:store:set --encrypt payment/adyen_abstract/api_key_test "${ADYEN_API_KEY}" > /dev/null
	echo "configuration step ended"

# Clear cache
flush:
	bin/magento cache:flush

# Setup permissions
fs:
	find var generated vendor pub/static pub/media app/etc -type f -exec chmod g+w {} +
	find var generated vendor pub/static pub/media app/etc -type d -exec chmod g+ws {} +
	chmod 777 -R var
	chown -R www-data:www-data .
	chmod u+x bin/magento
	echo "memory_limit = -1" > /usr/local/etc/php/conf.d/memory.ini

# Production mode
production:
	bin/magento deploy:mode:set production

refresh:

	bin/magento setup:upgrade
	bin/magento setup:di:compile
	bin/magento setup:static-content:deploy -f
	bin/magento cache:flush

MAGENTO_ROOT=/var/www/html
