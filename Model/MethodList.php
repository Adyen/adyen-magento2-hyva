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
     * @param AdyenHyvaConfigProvider $adyenHyvaConfigProvider
     */
    public function __construct(
        AdyenHyvaConfigProvider $adyenHyvaConfigProvider
    ) {
        $this->adyenHyvaConfigProvider = $adyenHyvaConfigProvider;
    }

    /**
     * @return array
     */
    public function collectAvailableMethods(): array
    {
        return array_keys($this->adyenHyvaConfigProvider->getPaymentMethodTxVariants());
    }
}
