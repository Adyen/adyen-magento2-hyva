<?php

namespace Adyen\Hyva\Magewire\Payment\Method;

use Exception;
use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;
use Adyen\Hyva\Api\ProcessingMetadataInterface;

class GooglePay extends AdyenPaymentComponent implements EvaluationInterface
{
    /**
     * @inheritDoc
     */
    function getMethodCode(): string
    {
        return ProcessingMetadataInterface::METHOD_GOOGLE_PAY;
    }

    /**
     * {@inheritDoc}
     */
    public function evaluateCompletion(EvaluationResultFactory $resultFactory): EvaluationResultInterface
    {
        return $resultFactory->createBlocking();
    }
}
