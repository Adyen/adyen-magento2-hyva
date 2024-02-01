<?php

namespace Adyen\Hyva\Magewire\Payment\Method;

use Adyen\Hyva\Api\ProcessingMetadataInterface;
use Adyen\Hyva\Model\Configuration;
use Adyen\Hyva\Model\CreditCard\BrandsManager;
use Adyen\Hyva\Model\PaymentMethod\PaymentMethods;
use Adyen\Payment\Api\AdyenOrderPaymentStatusInterface;
use Adyen\Payment\Api\AdyenPaymentsDetailsInterface;
use Adyen\Payment\Helper\StateData;
use Adyen\Payment\Helper\Util\CheckoutStateDataValidator;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Checkout\Model\Session;

class CreditCard extends AdyenPaymentComponent
{
    private BrandsManager $brandsManager;

    public function __construct(
        CheckoutStateDataValidator $checkoutStateDataValidator,
        Configuration $configuration,
        Session $session,
        StateData $stateData,
        PaymentMethods $paymentMethodsHelper,
        PaymentInformationManagementInterface $paymentInformationManagement,
        AdyenOrderPaymentStatusInterface $adyenOrderPaymentStatus,
        AdyenPaymentsDetailsInterface $adyenPaymentsDetails,
        BrandsManager $brandsManager
    ) {
        parent::__construct(
            $checkoutStateDataValidator,
            $configuration,
            $session,
            $stateData,
            $paymentMethodsHelper,
            $paymentInformationManagement,
            $adyenOrderPaymentStatus,
            $adyenPaymentsDetails
        );

        $this->brandsManager = $brandsManager;
    }

    /**
     * {@inheritDoc}
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
    public function getBrands(): string
    {
        return $this->brandsManager->getBrands();
    }
}
