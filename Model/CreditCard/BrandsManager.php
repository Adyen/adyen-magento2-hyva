<?php

declare(strict_types=1);

namespace Adyen\Hyva\Model\CreditCard;

use Adyen\Payment\Api\AdyenPaymentMethodManagementInterface;
use Magento\Checkout\Model\Session;

class BrandsManager
{
    public function __construct(
        private Session $session,
        private AdyenPaymentMethodManagementInterface $adyenPaymentMethodManagement
    ) {
    }

    /**
     * @return string
     */
    public function getBrands(): string
    {
        try {
            if ($this->session->getQuote()->getId()) {
                $paymentMethodsResponse = json_decode(
                    $this->adyenPaymentMethodManagement->getPaymentMethods(
                        strval($this->session->getQuote()->getId())
                    ),
                    true
                );

                if (isset($paymentMethodsResponse['paymentMethodsResponse']['paymentMethods'])) {
                    foreach ($paymentMethodsResponse['paymentMethodsResponse']['paymentMethods'] as $paymentMethod) {
                        if ($paymentMethod['type'] == 'scheme') {
                            return json_encode($paymentMethod['brands']);
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            return json_encode([]);
        }

        return json_encode([]);
    }
}
