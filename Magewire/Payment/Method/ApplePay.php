<?php

declare(strict_types=1);

namespace Adyen\Hyva\Magewire\Payment\Method;

use Hyva\Checkout\Model\Magewire\Component\Evaluation\EvaluationResult;
use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;

class ApplePay extends AdyenPaymentComponent implements EvaluationInterface
{
    const METHOD_APPLE_PAY = 'adyen_applepay';

    /**
     * @inheritDoc
     */
    function getMethodCode(): string
    {
        return self::METHOD_APPLE_PAY;
    }

    /**
     * {@inheritDoc}
     */
    public function evaluateCompletion(EvaluationResultFactory $resultFactory): EvaluationResult
    {
        return $resultFactory->createBlocking();
    }
}
