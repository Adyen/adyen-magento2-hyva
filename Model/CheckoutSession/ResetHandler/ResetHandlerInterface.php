<?php

declare(strict_types=1);

namespace Adyen\Hyva\Model\CheckoutSession\ResetHandler;

use Magento\Quote\Api\Data\CartInterface;

interface ResetHandlerInterface
{
    /**
     * @param CartInterface $cart
     * @return bool
     */
    public function delayReset(CartInterface $cart): bool;
}
