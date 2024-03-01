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
     * @return array
     */
    public function getBrandsAsArray(): array
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
                            return $paymentMethod['brands'];
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            return [];
        }

        return [];
    }

    /**
     * @return string
     */
    public function getBrands(): string
    {
        $brands = $this->getBrandsAsArray();

        return json_encode($brands);
    }
}
