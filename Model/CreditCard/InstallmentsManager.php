<?php

declare(strict_types=1);

namespace Adyen\Hyva\Model\CreditCard;

use Adyen\Payment\Helper\Config;
use Adyen\Payment\Helper\Data;
use Adyen\Payment\Helper\Installments;
use Adyen\Payment\Logger\AdyenLogger;
use Magento\Checkout\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Throwable;

class InstallmentsManager
{
    public function __construct(
        private Session $session,
        private Installments $installmentsHelper,
        private Config $configHelper,
        private StoreManagerInterface $storeManager,
        private Data $adyenHelper,
        private AdyenLogger $adyenLogger
    ) {

    }

    /**
     * @return string
     */
    public function getFormattedInstallments(): string
    {
        try {
            $quoteData = $this->session->getQuote()->getData();
            $amount = $quoteData['grand_total'];

            return $this->installmentsHelper->formatInstallmentsConfig(
                $this->configHelper->getAdyenCcConfigData('installments',
                    $this->storeManager->getStore()->getId()
                ),
                $this->adyenHelper->getAdyenCcTypes(),
                $amount
            );
        } catch (Throwable $e) {
            $this->adyenLogger->error(
                'There was an error fetching the installments config: ' . $e->getMessage()
            );
            return '{}';
        }
    }
}
