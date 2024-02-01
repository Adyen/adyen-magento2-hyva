<?php

namespace Adyen\Hyva\Magewire\Payment\Method;

use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;
use Adyen\Hyva\Api\ProcessingMetadataInterface;

class Paypal extends AdyenPaymentComponent implements EvaluationInterface
{
    /**
     * @inheritDoc
     */
    function getMethodCode(): string
    {
        return ProcessingMetadataInterface::METHOD_PAYPAL;
    }

    /**
     * {@inheritDoc}
     */
    public function evaluateCompletion(EvaluationResultFactory $resultFactory): EvaluationResultInterface
    {
        return $resultFactory->createBlocking();
    }
}
