<?php

namespace Adyen\Hyva\Model\Component\Payment;

use Adyen\Hyva\Model\Configuration;
use Adyen\Hyva\Model\Customer\CustomerGroupHandler;
use Adyen\Hyva\Model\PaymentMethod\PaymentMethods;
use Adyen\Payment\Api\AdyenOrderPaymentStatusInterface;
use Adyen\Payment\Api\AdyenPaymentsDetailsInterface;
use Adyen\Payment\Helper\StateData;
use Adyen\Payment\Helper\Util\CheckoutStateDataValidator;
use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\ObjectManager\ContextInterface;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Psr\Log\LoggerInterface;

class Context implements ContextInterface
{
    public function __construct(
        private readonly CheckoutStateDataValidator $checkoutStateDataValidator,
        private readonly Configuration $configuration,
        private readonly Session $session,
        private readonly StateData $stateData,
        private readonly PaymentMethods $paymentMethods,
        private readonly PaymentInformationManagementInterface $paymentInformationManagement,
        private readonly GuestPaymentInformationManagementInterface $guestPaymentInformationManagement,
        private readonly QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId,
        private readonly AdyenOrderPaymentStatusInterface $adyenOrderPaymentStatus,
        private readonly AdyenPaymentsDetailsInterface $adyenPaymentsDetails,
        private readonly CustomerGroupHandler $customerGroupHandler,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @return CheckoutStateDataValidator
     */
    public function getCheckoutStateDataValidator(): CheckoutStateDataValidator
    {
        return $this->checkoutStateDataValidator;
    }

    /**
     * @return Configuration
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    /**
     * @return Session
     */
    public function getSession(): Session
    {
        return $this->session;
    }

    /**
     * @return StateData
     */
    public function getStateData(): StateData
    {
        return $this->stateData;
    }

    /**
     * @return PaymentMethods
     */
    public function getPaymentMethods(): PaymentMethods
    {
        return $this->paymentMethods;
    }

    /**
     * @return PaymentInformationManagementInterface
     */
    public function getPaymentInformationManagement(): PaymentInformationManagementInterface
    {
        return $this->paymentInformationManagement;
    }

    /**
     * @return PaymentInformationManagementInterface
     */
    public function getGuestPaymentInformationManagement(): GuestPaymentInformationManagementInterface
    {
        return $this->guestPaymentInformationManagement;
    }

    /**
     * @return QuoteIdToMaskedQuoteIdInterface
     */
    public function getQuoteIdToMaskedQuoteId(): QuoteIdToMaskedQuoteIdInterface
    {
        return $this->quoteIdToMaskedQuoteId;
    }

    /**
     * @return AdyenOrderPaymentStatusInterface
     */
    public function getAdyenOrderPaymentStatus(): AdyenOrderPaymentStatusInterface
    {
        return $this->adyenOrderPaymentStatus;
    }

    /**
     * @return AdyenPaymentsDetailsInterface
     */
    public function getAdyenPaymentsDetails(): AdyenPaymentsDetailsInterface
    {
        return $this->adyenPaymentsDetails;
    }

    public function getCustomerGroupHandler(): CustomerGroupHandler
    {
        return $this->customerGroupHandler;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
