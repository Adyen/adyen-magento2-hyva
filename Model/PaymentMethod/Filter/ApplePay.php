<?php

namespace Adyen\Hyva\Model\PaymentMethod\Filter;

use Adyen\Hyva\Magewire\Payment\Method\ApplePay as ApplePayComponent;
use Magento\Framework\App\Request\Http as HttpRequest;

/**
 * Handles the appearance of the Apple Pay payment method
 */
class ApplePay implements FilterInterface
{
    public function __construct(
        private readonly HttpRequest $httpRequest
    ) {

    }
    /**
     * {@inheritDoc}
     */
    public function execute(int $quoteId, array $list): array
    {
        $user_agent = $this->httpRequest->getServerValue('HTTP_USER_AGENT');;

        if (!preg_match('/chrome/i', $user_agent) && preg_match('/safari/i', $user_agent)) {
            return $list;
        }

        foreach ($list as $key => $method) {
            if ($method->getCode() == ApplePayComponent::METHOD_APPLE_PAY) {
                unset($list[$key]);
            }
        }

        return $list;
    }
}
