<?php

declare(strict_types=1);

namespace Adyen\Hyva\Magewire\Payment\Method;

use Hyva\Checkout\Model\Magewire\Component\Evaluation\EvaluationResult;
use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;

class Klarna extends AdyenPaymentComponent implements EvaluationInterface
{
    const METHOD_KLARNA = 'adyen_klarna';

    /**
     * @inheritDoc
     */
    function getMethodCode(): string
    {
        return self::METHOD_KLARNA;
    }

    /**
     * {@inheritDoc}
     */
    public function evaluateCompletion(EvaluationResultFactory $resultFactory): EvaluationResult
    {
        return $resultFactory->createBlocking();
    }
}
