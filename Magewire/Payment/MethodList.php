<?php

declare(strict_types=1);

namespace Adyen\Hyva\Magewire\Payment;

use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;

class MethodList extends \Hyva\Checkout\Magewire\Checkout\Payment\MethodList implements EvaluationInterface
{
    protected $listeners = [
        'billing_address_saved' => 'refresh',
        'shipping_address_saved' => 'refresh',
        'coupon_code_applied' => 'refresh',
        'coupon_code_revoked' => 'refresh',
        'shipping_address_activated' => 'refreshProperties',
        'billing_address_activated' => 'refreshProperties',
    ];

    protected $loader = [
        'method' => 'Saving method'
    ];

    /**
     * @return void
     */
    public function refreshProperties(): void
    {
        try {
            $this->dispatchBrowserEvent('adyen:payment_component:refresh',
                ['method' => $this->sessionCheckout->getQuote()->getPayment()->getMethod()]
            );
        } catch (\Exception $exception) {
        }
    }
}
