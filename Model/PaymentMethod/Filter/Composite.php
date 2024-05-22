<?php

namespace Adyen\Hyva\Model\PaymentMethod\Filter;

class Composite implements FilterInterface
{
    private array $filters = [];

    public function __construct(
        array $filters = []
    ) {
        foreach ($filters as $filter) {
            if ($filter instanceof FilterInterface) {
                $this->filters[] = $filter;
            }
        }
    }
    public function execute(int $quoteId, array $list): array
    {
        /** @var FilterInterface $filter */
        foreach ($this->filters as $filter) {
            $list = $filter->execute($quoteId, $list);
        }

        return $list;
    }
}
