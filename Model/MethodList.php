<?php

declare(strict_types=1);

namespace Adyen\Hyva\Model;

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
     * Those payment method layouts are not generated dynamically.
     * They are added from hyva_checkout_components.xml
     * This override is required to include them in the available payment methods.
     *
     * @var array
     */
    public array $staticallyDefinedMethods;

    /**
     * @param AdyenHyvaConfigProvider $adyenHyvaConfigProvider
     * @param array $customMethodRenderers
     * @param array $staticallyDefinedMethods
     */
    public function __construct(
        AdyenHyvaConfigProvider $adyenHyvaConfigProvider,
        array $customMethodRenderers = [],
        array $staticallyDefinedMethods = []
    ) {
        $this->adyenHyvaConfigProvider = $adyenHyvaConfigProvider;
        $this->customMethodRenderers = $customMethodRenderers;
        $this->staticallyDefinedMethods = $staticallyDefinedMethods;
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

        return array_values(array_merge($availableMethods, $this->staticallyDefinedMethods));
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
     * @return bool
     */
    public function isStaticallyRenderedMethod(string $methodCode): bool
    {
        return array_key_exists($methodCode, $this->staticallyDefinedMethods);
    }
}
