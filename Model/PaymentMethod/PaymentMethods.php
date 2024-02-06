<?php

namespace Adyen\Hyva\Model\PaymentMethod;

use Adyen\Payment\Helper\PaymentMethods as AdyenPaymentMethods;

class PaymentMethods
{
    private AdyenPaymentMethods $adyenPaymentMethods;

    public function __construct(
        AdyenPaymentMethods $adyenPaymentMethods
    ) {
        $this->adyenPaymentMethods = $adyenPaymentMethods;
    }

    /**
     * @param int $quoteId
     * @return string
     */
    public function getData(int $quoteId): string
     {
         $paymentMethods = json_decode($this->adyenPaymentMethods->getPaymentMethods($quoteId), true);

         if (isset($paymentMethods['paymentMethodsResponse']['storedPaymentMethods'])) {
             unset($paymentMethods['paymentMethodsResponse']['storedPaymentMethods']);
         }

         return json_encode($paymentMethods);
     }
}
