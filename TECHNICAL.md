# Technical specifics of Adyen_Hyva module

This documents tries to outline the technical specifics in the integration of the Adyen Payments with the Hyva checkout.

## Magewire

Magewire is used in the implementation of this module.

For basic understanding of the concept, please try https://docs.hyva.io/checkout/hyva-checkout/magewire/index.html#under-the-hood

#### Magewire Components

This module defines the Magewire components under the `Adyen\Hyva\Magewire` namespace. 

More specifically, the components that are utilized in payment methods implementation are found under the `Adyen\Hyva\Magewire\Payment\Method` namespace.

Here the `AdyenPaymentComponent` abstract class is defined in an attempt to solve common concerns accross different payment options.

#### PHTML and Magewire binding for a payment method

Each payment method would have its own .phtml file associated with it. The mapping is done in 

```
/view/frontend/layout/hyva_checkout_components.xml
``` 

and will bind the Magewire component to the .phthml. An example may look like:

```
<page>
    <body>
        <referenceBlock name="checkout.payment.methods" template="Adyen_Hyva::checkout/payment/method-list.phtml">
            <block name="checkout.payment.method.adyen_cc"
                   as="adyen_cc"
                   template="Adyen_Hyva::payment/method-renderer/adyen-cc-method.phtml">
                <arguments>
                    <argument name="magewire" xsi:type="object">Adyen\Hyva\Magewire\Payment\Method\CreditCard</argument>
                </arguments>
            </block>  
        </referenceBlock>
    </body>
</page>
```

Please notice that implicitly the native Magento Template Block `Magento\Framework\View\Element\Template` is used. 
It is possible to further specify a custom block class if needed.

#### Solving common JS problems

Ideally, under the Hyva/Magewire stack, it should not be needed to write any particular java scripts. 
However, this module comes with one common Java Script class, that is used for handling frontend related functionality accross different payment methods.

This class is named `componentHandler` and is defined in the `view/frontend/templates/payment/model/adyen-payment-method.phtml` file.


This .phtml file is made part of the layout by `layout/hyva_checkout_index_index.phtml`


        <referenceContainer name="head.additional">
            <block name="adyen.hyva.payment.initialize" template="Adyen_Hyva::payment/init/init.phtml" after="-">
                <block class="Adyen\Hyva\Block\PaymentMethod"
                       name="checkout.payment.method.model.common"
                       as="checkout.payment.method.model.common"
                       template="Adyen_Hyva::payment/model/adyen-payment-method.phtml">
                </block>
            </block>
        </referenceContainer>
        
Please note that the parent `init.phtml` file is responsible to load the original adyen.js library.

#### Payment method implementation

Usually, the flow of implementing a payment method, consists of the following steps:

- listen to an event that is supposed to trigger adyen component load
- collect component configuration
- build the component
- mount/activate the component

Upon interaction with the component, some extra actions may be taken, like `onChange` we might need to block the `Place Order` button, or `onBrand` we might need to recollect new card variation specific info.

Please follow an example of the above mentioned steps in a method-renderer .phtml like `adyen-cc-method.phtml`.

#### Placing an order

The placement of the order happens in two general ways:
- uses the original hyva checkout "Place Order" button, which is true for methods like "Credit Card" and "Vault/Stored Card"
- uses the button that is loaded within a popup, that is Wallet payment method specific

These two cases will have a different implementation, and things will look differently. For example for "Credit Card"

```
let component = await creditCardHandler.buildComponent(
    <?= /* @noEscape */ $magewire->getPaymentResponse() ?>.paymentMethodsResponse
);

creditCardHandler.mountComponent(component, creditCardConfiguration.type, configuration);

hyvaCheckout.payment.activate('<?= $magewire->getMethodCode() ?>',
    {
        async placeOrder() {
            let stateData = creditCardHandler.getActionComponent().data;
            creditCardHandler.placeOrder(stateData);
        },
        placeOrderViaJs() {
            return true
        }
    },
    document.getElementById('CreditCardActionContainer')
);
```

Please notice that 
- when building the component we do not specify of what needs to happen `handleOnSubmit`
- the original hyva `placeOrder` JS method is overwritten

In contrast, when dealing with Wallet methods, like for example GPay, things would look like

```
let configuration = googlePayHandler.buildConfiguration(googlepayMethodConfiguration, paymentMethodsExtraInfo);

let component = await googlePayHandler.buildComponent(
    <?= /* @noEscape */ $magewire->getPaymentResponse() ?>.paymentMethodsResponse,
    function (result) {
        googlePayHandler.handleAdditionalDetails(result.data)
    },
    function (result) {},
    function (state) {
        googlePayHandler.placeOrder(state.data);
    }
);
```

Here we can notice that

- we do not need to overwrite the hyve `placeOrder` JS method
- we do need to specify how handle things on submit, using
    ```
    function (state) {
        googlePayHandler.placeOrder(state.data);
    }
    ```
  
  
#### Backend interaction

Magewire is designed to be flexible enough so to simply ask the backend for some information, or, trigger some backend methods to execute and then collect information about the updated state.

One way to do it, as for example might look like

```
wire.processQuoteParameters(cardInstallments)
    .then(() => {
        let installmentOptions = JSON.parse(wire.get('installmentOptions'));

        if (installmentOptions.length > 1) {
            creditCardHandler.addInstallmentOptions(installmentOptions)
        }
    }).catch(() => {
        console.log('Error occurred during installments processing')
});
```

In this example
- we do make a call to a backend compoment method called `processQuoteParameters` (see `AdyenPaymentComponent` for details), sending the array of data
- the backend php method does its own processing, while setting some information to its public property
    ```public ?string $installmentOptions = null;```
- using the `then` portion of the promise, we address the backend one more time to read the value of the public property
    ```wire.get('installmentOptions')```
    
    
## Dependency on the Hyva Checkout module

The current version used of the HyvaCheckout (v1.1.0 (to verify!)) does NOT support out of the box a couple of behaviors.

This is why the RC1 version of Adyen_Hyva has do to a couple of overwrites and customization, with the plan to eventually remove and refactor them once they become available in next HyvaCheckout versions.

A. Stored cards implementation is not yet supported in HyvaCheckout

This module does its own implementation, but in order to do it, both the overwrite of the `method-list.phtml` and `Adyen\Hyva\Plugin\HyvaCheckout\Model\Magewire\Component\Resolver\Checkout` plugin are necessary.


B. Current `Hyva\Checkout\Observer\Frontend\HyvaCheckoutSessionReset` is unconditional at the moment.

Before a new, improved way of reseting the session is obtained, this module does disable of the original hyva observer, and introduces a custom one with

```
<event name="checkout_submit_all_after">
    <observer name="CheckoutReset_HyvaCheckoutCheckoutSubmitAllAfter" disabled="true" />
    <observer name="Adyen_Hyva_CheckoutSubmitAllAfter" instance="Adyen\Hyva\Observer\HyvaCheckoutSessionReset"/>
</event>
```

This custom observer relies on `Adyen\Hyva\Model\CheckoutSession\ResetHandlerPool` to decide under which circumstances to reset the session.
