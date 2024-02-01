<?php

namespace Adyen\Hyva\Model\CheckoutSession\ResetHandler;

use Adyen\Hyva\Model\MethodList;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Based on the idea coming from Adyen Payment module:
 *    - the quote is kept active when customer challenge is necessary!
 *
 * Therefore, we keep the checkout config session alive as well
 *
 * Reference: Adyen\Payment\Observer\SubmitQuoteObserver
 */
class QuoteStatus implements ResetHandlerInterface
{
    private MethodList $methodList;

    public function __construct(
        MethodList $methodList
    ) {
        $this->methodList = $methodList;
    }

    /**
     * @inheritDoc
     */
    public function delayReset(CartInterface $cart): bool
    {
        if ($cart->getIsActive()
            && in_array($cart->getPayment()->getMethod(), $this->methodList->collectAvailableMethods())
        ) {
            return true;
        }

        return false;
    }
}
