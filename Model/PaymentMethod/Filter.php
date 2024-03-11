<?php

namespace Adyen\Hyva\Model\PaymentMethod;

use Adyen\Hyva\Model\PaymentMethod\Filter\Composite as CompositreFilter;
use Adyen\Hyva\Model\PaymentMethod\Filter\FilterInterface;

class Filter implements FilterInterface
{
    public function __construct(
        private readonly CompositreFilter $compositeFilter
    ) {

    }

    /**
     * {@inheritDoc}
     */
    public function execute(int $quoteId, array $list): array
    {
        return $this->compositeFilter->execute($quoteId, $list);
    }
}
