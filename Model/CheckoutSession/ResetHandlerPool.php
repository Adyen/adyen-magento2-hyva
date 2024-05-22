<?php

declare(strict_types=1);

namespace Adyen\Hyva\Model\CheckoutSession;

use Adyen\Hyva\Model\CheckoutSession\ResetHandler\ResetHandlerInterface;
use Magento\Quote\Api\Data\CartInterface;

class ResetHandlerPool implements ResetHandlerInterface
{
    /**
     * @var ResetHandlerInterface[]
     */
    private array $resetHandlers = [];

    public function __construct(
        array $resetHandlers = []
    ) {
        foreach ($resetHandlers as $resetHandler) {
            if ($resetHandler instanceof ResetHandlerInterface) {
                $this->resetHandlers[] = $resetHandler;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function delayReset(CartInterface $cart): bool
    {
        foreach ($this->resetHandlers as $resetHandler) {
            if ($resetHandler->delayReset($cart)) {
                return true;
            }
        }

        return false;
    }
}
