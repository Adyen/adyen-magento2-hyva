<?php

declare(strict_types=1);

namespace Adyen\Hyva\Magewire\Payment\Method;

use Hyva\Checkout\Model\Magewire\Component\Evaluation\EvaluationResult;
use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;

class BcmcMobile extends AdyenPaymentComponent implements EvaluationInterface
{
    const METHOD_BCMC_MODILE = 'adyen_bcmc_mobile';

    /**
     * @inheritDoc
     */
    function getMethodCode(): string
    {
        return self::METHOD_BCMC_MODILE;
    }
}
