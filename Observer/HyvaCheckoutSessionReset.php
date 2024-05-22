<?php

declare(strict_types=1);

namespace Adyen\Hyva\Observer;

use Adyen\Hyva\Model\CheckoutSession\ResetHandlerPool;
use Hyva\Checkout\Model\Session as SessionCheckoutConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class HyvaCheckoutSessionReset implements ObserverInterface
{
    public function __construct(
        private ResetHandlerPool $resetHandlerPool,
        private SessionCheckoutConfig $sessionCheckoutConfig
    ) {
    }

    public function execute(Observer $observer): void
    {
        $quote = $observer->getData('quote');

        if (!$this->resetHandlerPool->delayReset($quote)) {
            $this->sessionCheckoutConfig->reset();
        }
    }
}
