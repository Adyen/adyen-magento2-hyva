<?php

namespace Adyen\Hyva\Plugin\ViewModel\Checkout\Payment;

use Magento\Checkout\Model\Session as CheckoutSession;
use Adyen\Payment\Helper\PaymentMethodsFilter;
use Hyva\Checkout\ViewModel\Checkout\Payment\MethodList as HyvaMethodList;

class MethodList
{
    private $helper;

    private $checkoutSession;

    public function __construct(
        CheckoutSession $checkoutSession,
        PaymentMethodsFilter $helper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
    }

    public function afterGetList(HyvaMethodList $subject, $result)
    {
        list($result, $adyenPaymentMethodsResponse) = $this->helper->sortAndFilterPaymentMethods($result, $this->checkoutSession->getQuote());
        return $result;
    }
}