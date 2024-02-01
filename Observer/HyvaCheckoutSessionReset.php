<?php

namespace Adyen\Hyva\Observer;

use Adyen\Hyva\Model\CheckoutSession\ResetHandlerPool;
use Hyva\Checkout\Model\Session as SessionCheckoutConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class HyvaCheckoutSessionReset implements ObserverInterface
{
    private ResetHandlerPool $resetHandlerPool;
    private SessionCheckoutConfig $sessionCheckoutConfig;

    public function __construct(
        ResetHandlerPool $resetHandlerPool,
        SessionCheckoutConfig $sessionCheckoutConfig
    ) {
        $this->sessionCheckoutConfig = $sessionCheckoutConfig;
        $this->resetHandlerPool = $resetHandlerPool;
    }

    public function execute(Observer $observer): void
    {
        $quote = $observer->getData('quote');

        if (!$this->resetHandlerPool->delayReset($quote)) {
            $this->sessionCheckoutConfig->reset();
        }
    }
}
