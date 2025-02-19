<?php

namespace Adyen\Hyva\Plugin\ViewModel\Checkout\Payment;

use Magento\Checkout\Model\Session as CheckoutSession;
use Adyen\Payment\Helper\PaymentMethodsFilter;
use Hyva\Checkout\ViewModel\Checkout\Payment\MethodList as HyvaMethodList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class MethodList
{
    /**
     * @param CheckoutSession $checkoutSession
     * @param PaymentMethodsFilter $paymentMethodsFilterHelper
     */
    public function __construct(
        private readonly CheckoutSession $checkoutSession,
        private readonly PaymentMethodsFilter $paymentMethodsFilterHelper
    ) { }

    /**
     * This plugin changes the sorting order of the payment methods on the checkout
     * based on the sorting order of the payment methods on Adyen Customer Area.
     *
     * @param HyvaMethodList $subject
     * @param $result
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterGetList(HyvaMethodList $subject, $result): array
    {
        list($sortedMagentoPaymentMethods, $adyenPaymentMethodsResponse) = $this->paymentMethodsFilterHelper
            ->sortAndFilterPaymentMethods($result, $this->checkoutSession->getQuote());

        return $sortedMagentoPaymentMethods ?? $result;
    }
}
