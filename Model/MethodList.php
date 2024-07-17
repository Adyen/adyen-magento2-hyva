<?php

declare(strict_types=1);

namespace Adyen\Hyva\Model;

use Adyen\Hyva\Magewire\Payment\Method\AdyenPaymentComponent;
use Adyen\Hyva\Model\Ui\AdyenHyvaConfigProvider;

class MethodList
{
    /**
     * @var AdyenHyvaConfigProvider
     */
    private AdyenHyvaConfigProvider $adyenHyvaConfigProvider;

    /**
     * @var array
     */
    public array $customMethodRenderers;

    /**
     * @var array
     */
    public array $customMagewireClasses;

    /**
     * @param AdyenHyvaConfigProvider $adyenHyvaConfigProvider
     * @param array $customMethodRenderers
     * @param array $customMagewireClasses
     */
    public function __construct(
        AdyenHyvaConfigProvider $adyenHyvaConfigProvider,
        array $customMethodRenderers = [],
        array $customMagewireClasses = []
    ) {
        $this->adyenHyvaConfigProvider = $adyenHyvaConfigProvider;
        $this->customMethodRenderers = $customMethodRenderers;
        $this->customMagewireClasses = $customMagewireClasses;
    }

    /**
     * @return array
     */
    public function collectAvailableMethods(): array
    {
        $paymentMethods = array_keys($this->adyenHyvaConfigProvider->getPaymentMethodTxVariants());
        $availableMethods = [];

        foreach ($paymentMethods as $paymentMethod) {
            $available = true;

            if ($this->adyenHyvaConfigProvider->isCustomRendererRequired($paymentMethod) &&
                !isset($this->customMethodRenderers[$paymentMethod])) {
                $available = false;
            }

            if ($available) {
                $availableMethods[] = $paymentMethod;
            }
        }

        return array_values(array_merge($availableMethods, array_keys($this->customMagewireClasses)));
    }

    /**
     * Returns the custom method renderer defined in di.xml
     *
     * @param string $methodCode
     * @return string|null
     */
    public function getCustomMethodRenderer(string $methodCode): ?string
    {
        return $this->customMethodRenderers[$methodCode] ?? null;
    }

    /**
     * Checks if the payment method has a statically rendered layout block
     *
     * @param string $methodCode
     * @return string|null
     */
    public function getCustomMagewireClass(string $methodCode): ?AdyenPaymentComponent
    {
        return $this->customMagewireClasses[$methodCode] ?? null;
    }
}
