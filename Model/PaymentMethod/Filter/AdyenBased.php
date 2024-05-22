<?php

namespace Adyen\Hyva\Model\PaymentMethod\Filter;

use Adyen\Hyva\Api\ProcessingMetadataInterface;
use Adyen\Hyva\Model\MethodList;

/**
 * Filter out methods that are Adyen Based, but are not supported in Hyva checkout
 */
class AdyenBased implements FilterInterface
{
    public function __construct(
        private readonly MethodList $methodList
    ) {
    }
    /**
     * {@inheritDoc}
     */
    public function execute(int $quoteId, array $list): array
    {
        foreach ($list as $key => $method) {
            if ($this->isMethodAdyenBased($method) && !$this->isMethodHyvaSupported($method)) {
                unset($list[$key]);
            }
        }

        return $list;
    }

    /**
     * @param $method
     * @return bool
     */
    private function isMethodAdyenBased($method): bool
    {
        if (str_starts_with($method->getCode(), ProcessingMetadataInterface::METHOD_ADYEN_PREFIX)) {
            return true;
        }

        return false;
    }

    /**
     * @param $method
     * @return bool
     */
    private function isMethodHyvaSupported($method): bool
    {
        if (in_array($method->getCode(), $this->methodList->collectAvailableMethods())) {
            return true;
        }

        return false;
    }
}
