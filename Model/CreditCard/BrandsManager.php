<?php

namespace Adyen\Hyva\Model\CreditCard;

use Adyen\Payment\Api\AdyenPaymentMethodManagementInterface;
use Magento\Checkout\Model\Session;

class BrandsManager
{
    private Session $session;
    private AdyenPaymentMethodManagementInterface $adyenPaymentMethodManagement;

    public function __construct(
        Session $session,
        AdyenPaymentMethodManagementInterface $adyenPaymentMethodManagement
    ) {
        $this->session = $session;
        $this->adyenPaymentMethodManagement = $adyenPaymentMethodManagement;
    }

    /**
     * @return string
     */
    public function getBrands(): string
    {
        try {
            $paymentMethodsResponse = json_decode(
                $this->adyenPaymentMethodManagement->getPaymentMethods(
                    $this->session->getQuoteId()
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
        } catch (\Exception $exception) {
            return json_encode([]);
        }

        return json_encode([]);
    }
}
