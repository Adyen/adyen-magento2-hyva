<?php

namespace Adyen\Hyva\Magewire\Checkout;

use Magento\Checkout\Model\Session as SessionCheckout;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CouponManagementInterface;
use Magewirephp\Magewire\Component;

class CouponCode extends Component
{
    public ?string $couponCode = null;
    public int $couponHits = 0;

    protected CouponManagementInterface $couponManagement;
    protected SessionCheckout $sessionCheckout;

    public function __construct(
        CouponManagementInterface $couponManagement,
        SessionCheckout $sessionCheckout
    ) {
        $this->couponManagement = $couponManagement;
        $this->sessionCheckout = $sessionCheckout;
    }

    /**
     * @throws NoSuchEntityException
     */
    public function boot(): void
    {
        $couponCode = $this->couponManagement->get($this->sessionCheckout->getQuoteId());
        $this->couponCode = $couponCode ?? null;
    }

    public function applyCouponCode()
    {
        try {
            $quoteEntity = $this->sessionCheckout->getQuoteId();

            if (empty($this->couponCode)) {
                throw new LocalizedException(
                    __('No Coupon')
                );
            }
            if (! empty($this->couponManagement->get($quoteEntity))) {
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
            if (empty($this->couponCode)) {
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
