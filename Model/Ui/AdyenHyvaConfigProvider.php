<?php

declare(strict_types=1);

namespace Adyen\Hyva\Model\Ui;

use Adyen\Payment\Model\Ui\AdyenGenericConfigProvider;

class AdyenHyvaConfigProvider extends AdyenGenericConfigProvider
{
    /**
     * Returns Adyen payment method TX variants
     *
     * @return array
     */
    public function getPaymentMethodTxVariants(): array
    {
        return $this->txVariants;
    }

    /**
     * Check whether given payment method requires a custom renderer or not
     *
     * @param string $methodName
     * @return bool
     */
    public function isCustomRendererRequired(string $methodName): bool
    {
        $customMethodRenderers = $this->getCustomMethodRenderers();

        return key_exists($methodName, $customMethodRenderers);
    }

    /**
     * Returns the list of payment methods which require custom method renderers
     *
     * @return array
     */
    private function getCustomMethodRenderers(): array
    {
        return $this->customMethodRenderers;
    }
}
