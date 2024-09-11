# Set up SSH config for gitlab.hyva.io
echo "$SSH_PRIVATE_KEY" >> /root/.ssh/hyva_id_rsa
chmod 600 /root/.ssh/hyva_id_rsa
ssh-keyscan gitlab.hyva.io >> /root/.ssh/known_hosts
echo "Host gitlab.hyva.io" >> /root/.ssh/config
echo "  StrictHostKeyChecking no" >> /root/.ssh/config
echo "  IdentityFile /root/.ssh/hyva_id_rsa" >> /root/.ssh/config
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
composer config --json repositories.local '{"type": "path", "url": "/data/extensions/workdir", "options": { "symlink": false } }'
composer require adyen/module-hyva-checkout:*

bin/magento module:enable --all
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:clean


service php${PHP_VERSION}-fpm restart
#service nginx restart

/etc/init.d/cron start

exec apache2-foreground
