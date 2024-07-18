<?php

declare(strict_types=1);

namespace Adyen\Hyva\Model;

use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;

class PaymentMethodBlock
{
    /**
     * @var string
     */
    protected string $methodName;

    /**
     * @var string
     */
    protected string $blockName;

    /**
     * @var string
     */
    protected string $template;

    /**
     * @var EvaluationInterface
     */
    protected EvaluationInterface $wire;

    /**
     * @return string
     */
    public function getMethodName(): string
    {
        return $this->methodName;
    }

    /**
     * @param string $methodName
     * @return $this
     */
    public function setMethodName(string $methodName): PaymentMethodBlock
    {
        $this->methodName = $methodName;

        return $this;
    }

    /**
     * @return string
     */
    public function getBlockName(): string
    {
        return $this->blockName;
    }

    /**
     * @param string $blockName
     * @return $this
     */
    public function setBlockName(string $blockName): PaymentMethodBlock
    {
        $this->blockName = $blockName;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @param string $template
     * @return $this
     */
    public function setTemplate(string $template): PaymentMethodBlock
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return EvaluationInterface
     */
    public function getWire(): EvaluationInterface
    {
        return $this->wire;
    }

    /**
     * @param EvaluationInterface $wire
     * @return $this
     */
    public function setWire(EvaluationInterface $wire): PaymentMethodBlock
    {
        $this->wire = $wire;

        return $this;
    }
}
