<?php

declare(strict_types=1);

namespace Adyen\Hyva\Model\PaymentMethod;

use Adyen\Payment\Helper\PaymentMethods as AdyenPaymentMethods;

class PaymentMethods
{
    public function __construct(
        private AdyenPaymentMethods $adyenPaymentMethods
    ) {
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
