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
    "adyen/module-payment": "^9.5.2",
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

The following payment methods are supported:
   
 - Credit card    
 - Stored (Tokenized) credit card    
 - Google pay
 - Apple Pay
 - Paypal
    
### Magewire usage

Each payment method implementation depends on the work of a magewire component. (https://github.com/magewirephp/magewire) 

Reference classes are located under the `Adyen\Hyva\Magewire\Payment\Method` namespace.

### PSI compliance 
When making an order, the state data is extracted from the request parameters, 
and the state data is temporarily attached (but never persisted) to the Adyen's native State Data Object (`Adyen\Payment\Helper\StateData`).
