<?php

declare(strict_types=1);

namespace Adyen\Hyva\Magewire\Payment\Method;

use Adyen\Hyva\Model\Component\Payment\Context;
use Adyen\Hyva\Model\CreditCard\BrandsManager;
use Adyen\Hyva\Model\CreditCard\InstallmentsManager;
use Hyva\Checkout\Model\Magewire\Component\Evaluation\EvaluationResult;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;

class CreditCard extends AdyenPaymentComponent
{
    const METHOD_CC = 'adyen_cc';

    public function __construct(
        private readonly Context $context,
        private readonly BrandsManager $brandsManager,
        private readonly InstallmentsManager $installmentsManager,

    ) {
        parent::__construct($this->context);
    }

    /**
     * {@inheritDoc}
     */
    public function getMethodCode(): string
    {
        return self::METHOD_CC;
    }

    /**
     * {@inheritDoc}
     */
    public function evaluateCompletion(EvaluationResultFactory $resultFactory): EvaluationResult
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

    /**
     * @return string
     */
    public function getFormattedInstallments(): string
    {
        return $this->installmentsManager->getFormattedInstallments();
    }
}
