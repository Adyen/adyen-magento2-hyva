> ## ⚠️ **Attention:**
> ## This integration is no longer officially supported and will not get new updates.
# Adyen Payment - Integration with Hyva Checkout

This module supports Adyen payments via the Hyva Checkout implementation for Magento 2.

### Dependencies

This module depends on:
- Adyen_Payment plugin
- The Hyva default theme
- Hyva_Checkout plugin

### Installation

The dependencies may be obtained like for example

```
    "adyen/module-payment": "^9.13.0",
    "hyva-themes/magento2-default-theme": "^1.3",
    "hyva-themes/magento2-hyva-checkout": "^1.1",
```

### Basic setup

The setup requires Adyen Payment configuration. This module does not introduce any custom configuration options, 
so Adyen Payment configuration is done as it would be otherwise done for any other default case (e.g. Luma based checkout).

Then, the setup requires that for a given store, the Hyva theme and the Hyva checkout are configured:

 - Navigate to the Content > Design > Configuration admin section and activate the hyva/default theme for a given store view
 - Navigate to the Stores > Configuration > Hyva Themes > Checkout > General and activate `Hyva Default` (or `Hyva One page`) for a given store view
### Supported methods

Please refer to [Adobe Commerce Supported Payment Methods](https://docs.adyen.com/plugins/adobe-commerce/supported-payment-methods/) documentation.
