<?php

namespace Adyen\Hyva\Block\Checkout;

use Adyen\Hyva\Model\ThemeConfiguration;
use Adyen\Payment\Helper\Config;
use Adyen\Payment\Helper\Data;
use Adyen\Payment\Model\Ui\AdyenCheckoutSuccessConfigProvider;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\QuoteIdToMaskedQuoteId;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;

class Success extends \Adyen\Payment\Block\Checkout\Success
{
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        QuoteIdToMaskedQuoteId $quoteIdToMaskedQuoteId,
        OrderFactory $orderFactory,
        Data $adyenHelper,
        Config $configHelper,
        AdyenCheckoutSuccessConfigProvider $configProvider,
        StoreManagerInterface $storeManager,
        SerializerInterface $serializerInterface,
        private ThemeConfiguration $themeConfiguration,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $checkoutSession,
            $customerSession,
            $quoteIdToMaskedQuoteId,
            $orderFactory,
            $adyenHelper,
            $configHelper,
            $configProvider,
            $storeManager,
            $serializerInterface,
            $data
        );
    }

    public function isHyvaThemeActive(): bool
    {
        return $this->themeConfiguration->isHyvaThemeActive();
    }
}
