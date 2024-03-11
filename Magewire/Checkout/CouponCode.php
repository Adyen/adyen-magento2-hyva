<?php

declare(strict_types=1);

namespace Adyen\Hyva\Magewire\Checkout;

use Magento\Checkout\Model\Session as SessionCheckout;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CouponManagementInterface;
use Magewirephp\Magewire\Component;
use Psr\Log\LoggerInterface;

class CouponCode extends Component
{
    public ?string $couponCode = null;
    public int $couponHits = 0;

    public function __construct(
        private CouponManagementInterface $couponManagement,
        private SessionCheckout $sessionCheckout,
        private LoggerInterface $logger,
    ) {
    }

    public function boot(): void
    {
        try {
            $this->couponCode = $this->couponManagement->get($this->sessionCheckout->getQuoteId());
        } catch (NoSuchEntityException $exception) {
            $this->couponCode = null;
            $this->logger->error('Could not find a coupon code or the quote: ' . $exception->getMessage());
        }
    }

    public function applyCouponCode()
    {
        try {
            $quoteEntity = $this->sessionCheckout->getQuoteId();

            if ($this->couponCode === null) {
                throw new LocalizedException(
                    __('No Coupon')
                );
            }
            if (!empty($this->couponManagement->get($quoteEntity))) {
                throw new LocalizedException(
                    __('A coupon is already applied to the cart. Please remove it to apply another')
                );
            }

            $this->couponManagement->set($quoteEntity, $this->couponCode);
            $this->reset(['couponHits']);
        } catch (LocalizedException $exception) {
            $this->couponCode = null;
            $this->couponHits++;

            return $this->dispatchWarningMessage($exception->getMessage());
        }

        $this->dispatchSuccessMessage('Your coupon was successfully applied.');
        $this->dispatchBrowserEvent('checkout:coupon:activate');
        $this->emit('coupon_code_applied');

        return true;
    }

    public function revokeCouponCode()
    {
        try {
            if ($this->couponCode === null) {
                throw new LocalizedException(__('No Coupon'));
            }

            $this->reset();
            $this->couponManagement->remove($this->sessionCheckout->getQuoteId());
        } catch (LocalizedException $exception) {
            return $this->dispatchWarningMessage($exception->getMessage());
        }

        $this->dispatchSuccessMessage('Your coupon was successfully removed.');
        $this->dispatchBrowserEvent('checkout:coupon:deactivate');
        $this->emit('coupon_code_revoked');

        return true;
    }
}
