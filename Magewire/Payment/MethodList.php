<?php

declare(strict_types=1);

namespace Adyen\Hyva\Magewire\Payment;

use Hyva\Checkout\Magewire\Checkout\Payment\MethodList as HyvaMethodList;

/**
 * Substitute for \Hyva\Checkout\Magewire\Checkout\Payment\MethodList
 */
class MethodList extends HyvaMethodList
{
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

    public function boot(): void
    {
        parent::boot();

        // This custom event notifies that the method list has rebooted
        $this->dispatchBrowserEvent('checkout:payment:method-list-boot', ['method' => $this->method]);
    }

    public function updatedMethod(string $value): string
    {
        $value = parent::updatedMethod($value);

        $this->dispatchBrowserEvent('checkout:payment:method-activate', ['method' => $value]);
        return $value;
    }
}

