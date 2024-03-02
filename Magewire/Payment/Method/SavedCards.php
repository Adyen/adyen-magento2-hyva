<?php

declare(strict_types=1);

namespace Adyen\Hyva\Magewire\Payment\Method;

use Adyen\Hyva\Api\ProcessingMetadataInterface;
use Adyen\Hyva\Model\Configuration;
use Adyen\Hyva\Model\CreditCard\InstallmentsManager;
use Adyen\Hyva\Model\PaymentMethod\PaymentMethods;
use Adyen\Payment\Api\AdyenOrderPaymentStatusInterface;
use Adyen\Payment\Api\AdyenPaymentsDetailsInterface;
use Adyen\Payment\Helper\StateData;
use Adyen\Payment\Helper\Util\CheckoutStateDataValidator;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Checkout\Model\Session;
use Psr\Log\LoggerInterface;

class SavedCards extends AdyenPaymentComponent
{
    public function __construct(
        protected CheckoutStateDataValidator $checkoutStateDataValidator,
        protected Configuration $configuration,
        protected Session $session,
        protected StateData $stateData,
        protected PaymentMethods $paymentMethodsHelper,
        protected PaymentInformationManagementInterface $paymentInformationManagement,
        protected AdyenOrderPaymentStatusInterface $adyenOrderPaymentStatus,
        protected AdyenPaymentsDetailsInterface $adyenPaymentsDetails,
        private readonly InstallmentsManager $installmentsManager,
        protected LoggerInterface $logger
    ) {
        parent::__construct(
            $checkoutStateDataValidator,
            $configuration,
            $session,
            $stateData,
            $paymentMethodsHelper,
            $paymentInformationManagement,
            $adyenOrderPaymentStatus,
            $adyenPaymentsDetails,
            $logger
        );
    }

    /**
     * @return string
     */
    public function getMethodCode(): string
    {
        return ProcessingMetadataInterface::METHOD_CC;
    }

    /**
     * {@inheritDoc}
     */
    public function evaluateCompletion(EvaluationResultFactory $resultFactory): EvaluationResultInterface
    {
        return $resultFactory->createSuccess();
    }

    /**
     * @return string
     */
    public function getFormattedInstallments(): string
    {
        return $this->installmentsManager->getFormattedInstallments();
    }

    /**
     * @inheritDoc
     */
    public function placeOrder(array $data): void
    {
        $this->handleSessionVariables($data);
        $quotePayment = $this->session->getQuote()->getPayment();
        $quotePayment->setMethod(ProcessingMetadataInterface::METHOD_SAVED_CC);

        parent::placeOrder($data);
    }

    /**
     * @param array $data
     */
    private function handleSessionVariables(array $data)
    {
        //Clean the public hash value
        $this->session->setSavedCardPublicHash(null);

        //Set with input data if applicable
        if (isset($data[ProcessingMetadataInterface::POST_KEY_PUBLIC_HASH])) {
            $this->session->setSavedCardPublicHash($data[ProcessingMetadataInterface::POST_KEY_PUBLIC_HASH]);
        }
    }
}
