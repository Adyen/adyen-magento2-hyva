<?php

namespace Adyen\Hyva\ViewModel;

use Adyen\Hyva\Model\CreditCard\SavedCardsManager;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class SavedCards implements ArgumentInterface
{
    private SavedCardsManager $savedCardsManager;

    public function __construct(
        SavedCardsManager $savedCardsManager
    ) {
        $this->savedCardsManager = $savedCardsManager;
    }

    public function getStoredCards()
    {
        return $this->savedCardsManager->getStoredCards();
    }
}
