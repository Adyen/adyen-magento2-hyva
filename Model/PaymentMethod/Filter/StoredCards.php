<?php

namespace Adyen\Hyva\Model\PaymentMethod\Filter;

use Adyen\Hyva\Magewire\Payment\Method\StoredCards as StoredCardsComponent;
use Adyen\Hyva\Model\CreditCard\StoredCardsManager;

/**
 * Handles appearance of stored cards
 */
class StoredCards implements FilterInterface
{
    public function __construct(
        private readonly StoredCardsManager $storedCardsManager
    ) {

    }

    /**
     * {@inheritDoc}
     */
    public function execute(int $quoteId, array $list): array
    {
        $storedCards = $this->storedCardsManager->getStoredCards();

        if (empty($storedCards)) {
            foreach ($list as $key => $method) {
                if ($method->getCode() == StoredCardsComponent::METHOD_STORED_CC) {
                    unset($list[$key]);
                }
            }
        }

        return $list;
    }
}
