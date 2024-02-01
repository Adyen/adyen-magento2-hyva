<?php

namespace Adyen\Hyva\Model;

use Adyen\Hyva\Api\Data\StoredCreditCardInterface;
use Adyen\Hyva\Model\CreditCard\SavedCardsManager;

class MethodList
{
    private SavedCardsManager $savedCardsManager;
    private array $availableMethods = [];

    public function __construct(
        SavedCardsManager $savedCardsManager,
        $availableMethods = []
    ) {
        $this->availableMethods = $availableMethods;
        $this->savedCardsManager = $savedCardsManager;
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
