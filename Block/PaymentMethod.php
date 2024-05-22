<?php

declare(strict_types=1);

namespace Adyen\Hyva\Block;

use Adyen\Hyva\Model\Configuration;
use Adyen\Hyva\Model\MethodList;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\View\Element\Template;
use Magento\Quote\Model\Quote;

class PaymentMethod extends Template
{
    public function __construct(
        Template\Context $context,
        private Configuration $configuration,
        private MethodList $methodList,
        private Session $checkoutSession,
        private JsonSerializer $jsonSerializer,
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

    public function getAvailableMethods(): array
    {
        return $this->methodList->collectAvailableMethods();
    }

    public function getQuoteShippingAddress(): string
    {
        try {
            $quote = $this->getQuote();

            if ($quote && $quote->getShippingAddress()) {
                return $this->jsonSerializer->serialize($quote->getShippingAddress()->getData());
            }
        } catch (\InvalidArgumentException $exception) {
            return $this->defaultResponse();
        }

        return $this->defaultResponse();
    }

    public function getQuoteBillingAddress(): string
    {
        try {
            $quote = $this->getQuote();

            if ($quote && $quote->getBillingAddress()) {
                return $this->jsonSerializer->serialize($quote->getBillingAddress()->getData());
            }
        } catch (\InvalidArgumentException $exception) {
            return $this->defaultResponse();
        }


        return $this->defaultResponse();
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
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * @return string
     */
    private function defaultResponse(): string
    {
        return $this->jsonSerializer->serialize([]);
    }
}
