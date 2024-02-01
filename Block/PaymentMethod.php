<?php

namespace Adyen\Hyva\Block;

use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template;
use Adyen\Hyva\Model\Configuration;

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
        return json_encode($this->checkoutSession->getQuote()->getShippingAddress()->getData());
    }

    public function getQuoteBillingAddress()
    {
        return json_encode($this->checkoutSession->getQuote()->getBillingAddress()->getData());
    }
}
