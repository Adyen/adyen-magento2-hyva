<?php

declare(strict_types=1);

namespace Adyen\Hyva\Magewire\Payment\Method;

use Hyva\Checkout\Model\Magewire\Component\Evaluation\EvaluationResult;
use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;

class AbstractPaymentMethodWire extends AdyenPaymentComponent implements EvaluationInterface
{
    /**
     * @var string
     */
    protected string $methodCode;

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
    function getMethodCode(): string
    {
        return $this->methodCode;
    }

    /**
     * @param string $methodCode
     * @return $this
     */
    public function setMethodCode(string $methodCode): AbstractPaymentMethodWire
    {
        $this->methodCode = $methodCode;

        return $this;
    }

    public function getContainerName(): string
    {
        return str_replace('-', '', $this->getMethodCode());
    }
}
