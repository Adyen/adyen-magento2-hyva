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