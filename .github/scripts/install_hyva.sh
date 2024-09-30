# Set up SSH config for gitlab.hyva.io
echo "${SSH_PRIVATE_KEY}" >> ~/.ssh/hyva_id_rsa
chmod 600 ~/.ssh/hyva_id_rsa
ssh-keyscan gitlab.hyva.io >> ~/.ssh/known_hosts
echo "Host gitlab.hyva.io" >> ~/.ssh/config
echo "  StrictHostKeyChecking no" >> ~/.ssh/config
echo "  IdentityFile /var/www/.ssh/hyva_id_rsa" >> ~/.ssh/config
chmod 600 ~/.ssh/config

# Configure composer
echo "Configuring Composer with additional repositories"
composer config repositories.hyva-themes/hyva-checkout git git@gitlab.hyva.io:hyva-checkout/checkout.git
composer config repositories.hyva-themes/magento2-theme-module git git@gitlab.hyva.io:hyva-themes/magento2-theme-module.git
composer config repositories.hyva-themes/magento2-reset-theme git git@gitlab.hyva.io:hyva-themes/magento2-reset-theme.git
composer config repositories.hyva-themes/magento2-email-theme git git@gitlab.hyva.io:hyva-themes/magento2-email-module.git
composer config repositories.hyva-default-theme git git@gitlab.hyva.io:hyva-themes/magento2-default-theme.git

# Install Hyva compatibility module
echo "Installing Hyva compatibility module"
# TODO: update the branch from develop to current branch
echo "dev-${BRANCH_NAME}"
composer require adyen/module-hyva-checkout:dev-${BRANCH_NAME}

bin/magento module:enable --all
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:clean
