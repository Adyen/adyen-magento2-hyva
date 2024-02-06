<?php

namespace Adyen\Hyva\Block;

use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template;
use Adyen\Hyva\Model\Configuration;
use Magento\Quote\Model\Quote;

class PaymentMethod extends Template
{
    private Configuration $configuration;
    private Session $checkoutSession;

    public function __construct(
        Template\Context $context,
        Configuration $configuration,
        Session $checkoutSession,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->configuration = $configuration;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return Configuration
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function getQuoteShippingAddress()
    {
        $quote = $this->getQuote();

        if ($quote->getShippingAddress()) {
            return json_encode($quote->getShippingAddress()->getData());
        }

        return json_encode([]);
    }

    public function getQuoteBillingAddress()
    {
        $quote = $this->getQuote();

        if ($quote->getBillingAddress()) {
            return json_encode($quote->getBillingAddress()->getData());
        }

        return json_encode([]);
    }

    /**
     * @return Quote
     */
    private function getQuote(): Quote
    {
        /** @var Quote $quote */
        $quote = $this->checkoutSession->getQuote();

        return $quote;
    }
}
