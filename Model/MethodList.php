<?php

declare(strict_types=1);

namespace Adyen\Hyva\Model;

use Adyen\Hyva\Api\Data\StoredCreditCardInterface;
use Adyen\Hyva\Model\CreditCard\SavedCardsManager;

class MethodList
{
    public function __construct(
        private SavedCardsManager $savedCardsManager,
        private $availableMethods = []
    ) {
    }

    /**
     * @return array
     */
    public function collectAvailableMethods(): array
    {
        $storedCards = $this->getStoredCardsMethods();

        return array_unique(array_merge($this->availableMethods, $storedCards));
    }

    /**
     * @return array
     */
    private function getStoredCardsMethods(): array
    {
        $result = [];

        /** @var StoredCreditCardInterface $storedCard */
        foreach ($this->savedCardsManager->getStoredCards() as $storedCard) {
            $result[] = $storedCard->getLayoutId();
        }

        return $result;
    }
}
