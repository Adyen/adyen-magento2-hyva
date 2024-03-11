<?php

namespace Adyen\Hyva\Model\PaymentMethod\Filter;

interface FilterInterface
{
    /**
     * @param int $quoteId
     * @param array $list
     * @return array
     */
    public function execute(int $quoteId, array $list): array;
}
