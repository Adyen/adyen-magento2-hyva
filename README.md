#Adyen Payment - Integration with Hyva Checkout

This module supports Adyen payments via the Hyva Checkout implementation for Magento 2.

### Supported methods

The following payment methods are supported:
   
 - Credit card    
 - Saved (Tokenized) credit card    
 - Google pay
    
### Magewire usage

Each payment method implementation depends on the work of a magewire component. (https://github.com/magewirephp/magewire) 

Reference classes are located under the `Adyen\Hyva\Magewire\Payment\Method` namespace.

#### PSI compliance 
When making an order, the state data is extracted from the request parameters, 
and the state data is temporarily attached (but never persisted) to the Adyen's native State Data Object (`Adyen\Payment\Helper\StateData`).

#### Development Assumptions

This module is developed under a couple of assumptions:
- this module does not add its own configuration
- this module only reuses configuration that is coming from Adyen, it does not extend or alter in any way native Adyen configuration
- it assumes dependency on the `Adyen_payment` module, with the following ideas 
    - interfaces from the `Adyen_Payment` may be injected into classes of `Adyen_Hyva`
    - it must be avoided (to the extent that it is possible), to plugin to any, or take preference of any, native classes from the native `Adyen_Payment` module

