<?php

declare(strict_types=1);

namespace Adyen\Hyva\ViewModel;

use Adyen\Hyva\Model\CreditCard\StoredCardsManager;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class StoredCards implements ArgumentInterface
{
    public function __construct(
        private StoredCardsManager $storedCardsManager
    ) {
    }

    public function getStoredCards()
    {
        return $this->storedCardsManager->getStoredCards();
    }
}
