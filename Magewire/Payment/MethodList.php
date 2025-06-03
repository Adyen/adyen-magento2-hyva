<?php

declare(strict_types=1);

namespace Adyen\Hyva\Magewire\Payment;

use Hyva\Checkout\Magewire\Concern\Evaluatable;
use Hyva\Checkout\Model\ConfigData\HyvaThemes\Developer\SystemConfigExperimental;
use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;
use Magento\Checkout\Model\Session as SessionCheckout;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magewirephp\Magewire\Component;
use Psr\Log\LoggerInterface;

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

    protected SessionCheckout $sessionCheckout;
    protected CartRepositoryInterface $cartRepository;
    protected EvaluationResultFactory $evaluationResultFactory;
    protected SystemConfigExperimental $experimentalHyvaCheckoutConfig;
    protected PaymentMethodManagementInterface $paymentMethodManagement;

    public function __construct(
        SessionCheckout $sessionCheckout,
        CartRepositoryInterface $cartRepository,
        EvaluationResultFactory $evaluationResultFactory,
        SystemConfigExperimental|null $experimentalHyvaCheckoutConfig = null,
        PaymentMethodManagementInterface|null $paymentMethodManagement = null,
        private LoggerInterface|null $logger = null
    ) {
        $this->sessionCheckout = $sessionCheckout;
        $this->cartRepository = $cartRepository;
        $this->evaluationResultFactory = $evaluationResultFactory;

        $this->experimentalHyvaCheckoutConfig = $experimentalHyvaCheckoutConfig
            ?: ObjectManager::getInstance()->get(SystemConfigExperimental::class);
        $this->paymentMethodManagement = $paymentMethodManagement
            ?: ObjectManager::getInstance()->get(PaymentMethodManagementInterface::class);

        $this->logger ??= ObjectManager::getInstance()->get(LoggerInterface::class);
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
            $this->emit('payment_method_selected', ['method' => $quote->getPayment()->getMethod()]);
        } catch (LocalizedException $exception) {
            $this->dispatchErrorMessage('Something went wrong while saving your payment preferences.');
        }

        return $value;
    }

    public function evaluateCompletion(EvaluationResultFactory $resultFactory): EvaluationResultInterface
    {
        // Run all sub-evaluations.
        $this->evaluateSelection();

        if ($this->evaluationBatch()->containsFailureResults()) {
            return $this->evaluationBatch();
        }

        // Dispatches a success event to flag the component as completed.
        return $this->evaluationBatch()->push(
            $this->evaluationBatch()
                 ->factory()
                 ->createSuccess([], 'payment:method:success')
                 ->dispatch()
        );
    }

    private function evaluateSelection(): static
    {
        $method = $this->method;

        try {
            $quote = $this->sessionCheckout->getQuote();

            // Grep all available payment methods.
            $methods = $this->paymentMethodManagement->getList($quote->getId());
            // Transform the numeric keys into the payment method code.
            $methods = array_combine(array_map(fn (MethodInterface $object) => $object->getCode(), $methods), $methods);

            /*
             * Regardless of what happened during checkout, it’s possible that the previously selected
             * payment method is no longer available, while other methods still are.
             *
             * In this specific situation, we want to inform the user and allow them to select
             * a new available payment method, defaulting to the first available option.
             */
            if (count($methods) > 0 && is_string($method) && ! array_key_exists($method, $methods)) {
                $this->evaluationBatch()->push(
                    $this->evaluationBatch()->factory()

                        ->createMessageDialog('Payment Method')
                        ->withAlias('payment-method-not-available')
                        ->withMessage('The previous active payment method is no longer available. To complete your order, please select a different payment option.')
                        ->asInformally()
                );

                $method = null;
            }
        } catch (LocalizedException $exception) {
            $this->evaluationBatch()->push(
                $this->evaluationBatch()->factory()

                     ->createMessageDialog('Sorry, an unexpected error occurred')
                     ->presetAsTechnicalMalfunction()
            );

            return $this;
        }

        if ($method === null) {
            $this->evaluationBatch()->push(
                $this->evaluationBatch()->factory()

                    ->createErrorMessageEvent()
                    ->withCustomEvent('payment:method:error')
                    ->withMessage('The payment method is missing. Select the payment method and try again.')
            );
        }

        return $this;
    }
}

