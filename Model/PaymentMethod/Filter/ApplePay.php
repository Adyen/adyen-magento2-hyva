<?php

namespace Adyen\Hyva\Model\PaymentMethod\Filter;

use Magento\Framework\App\Request\Http as HttpRequest;

/**
 * Handles the appearance of the Apple Pay payment method
 */
class ApplePay implements FilterInterface
{
    const METHOD_APPLE_PAY = 'adyen_applepay';

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
            if ($method->getCode() == self::METHOD_APPLE_PAY) {
                unset($list[$key]);
            }
        }

        return $list;
    }
}
