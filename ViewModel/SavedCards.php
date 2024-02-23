<?php

declare(strict_types=1);

namespace Adyen\Hyva\ViewModel;

use Adyen\Hyva\Model\CreditCard\SavedCardsManager;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class SavedCards implements ArgumentInterface
{
    public function __construct(
        private SavedCardsManager $savedCardsManager
    ) {
    }

    public function getStoredCards()
    {
        return $this->savedCardsManager->getStoredCards();
    }
}
