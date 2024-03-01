<?php

declare(strict_types=1);

namespace Adyen\Hyva\Block;

use Adyen\Hyva\Model\Configuration;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Quote\Model\Quote;

class PaymentMethod extends Template
{
    public function __construct(
        Template\Context $context,
        private Configuration $configuration,
        private Session $checkoutSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return Configuration
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function getQuoteShippingAddress(): string
    {
        $quote = $this->getQuote();

        if ($quote && $quote->getShippingAddress()) {
            return json_encode($quote->getShippingAddress()->getData());
        }

        return json_encode([]);
    }

    public function getQuoteBillingAddress(): string
    {
        $quote = $this->getQuote();

        if ($quote && $quote->getBillingAddress()) {
            return json_encode($quote->getBillingAddress()->getData());
        }

        return json_encode([]);
    }

    /**
     * @return Quote|null
     */
    private function getQuote(): ?Quote
    {
        try {
            /** @var Quote $quote */
            $quote = $this->checkoutSession->getQuote();

            return $quote;
        } catch (\Exception $exception) {
            return null;
        }
    }
}
