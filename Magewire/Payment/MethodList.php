<?php

declare(strict_types=1);

namespace Adyen\Hyva\Magewire\Payment;

use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;
use Magento\Checkout\Model\Session as SessionCheckout;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magewirephp\Magewire\Component;

/**
 * Substitute for \Hyva\Checkout\Magewire\Checkout\Payment\MethodList
 */
class MethodList extends Component implements EvaluationInterface
{
    public ?string $method = null;

    protected $listeners = [
        'billing_address_saved' => 'refresh',
        'shipping_address_saved' => 'refresh',
        'coupon_code_applied' => 'refresh',
        'coupon_code_revoked' => 'refresh',
        //Refreshing the method list after the shipping method has been selected
        'shipping_method_selected' => 'refresh',
        //Refreshing the method list after the shipping address has been activated
        'shipping_address_activated' => 'refresh',
    ];

    protected $loader = [
        'method' => 'Saving method'
    ];

    public function __construct(
        protected readonly SessionCheckout $sessionCheckout,
        protected readonly CartRepositoryInterface $cartRepository,
        protected readonly EvaluationResultFactory $evaluationResultFactory
    ) {
    }

    public function boot(): void
    {
        try {
            $method = $this->sessionCheckout->getQuote()->getPayment()->getMethod();
        } catch (LocalizedException $exception) {
            $method = null;
        }

        $this->method = $method;
        //This custom event notifies that the method list has rebooted
        $this->dispatchBrowserEvent('checkout:payment:method-list-boot', ['method' => $method]);
    }

    public function updatedMethod(string $value): string
    {
        try {
            $quote = $this->sessionCheckout->getQuote();
            $quote->getPayment()->setMethod($value);

            $this->cartRepository->save($quote);

            $this->dispatchBrowserEvent('checkout:payment:method-activate', ['method' => $value]);
            $this->emit('payment_method_selected');
        } catch (LocalizedException $exception) {
            $this->dispatchErrorMessage('Something went wrong while saving your payment preferences.');
        }

        return $value;
    }

    public function evaluateCompletion(EvaluationResultFactory $resultFactory): EvaluationResultInterface
    {
        if ($this->method === null) {
            return $resultFactory->createErrorMessageEvent()
                ->withCustomEvent('payment:method:error')
                ->withMessage('The payment method is missing. Select the payment method and try again.');
        }

        return $resultFactory->createSuccess([], 'payment:method:success');
    }
}

