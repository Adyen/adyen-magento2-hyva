<?php

namespace Adyen\Hyva\Model\Customer;

use Magento\Checkout\Model\Session;
use Psr\Log\LoggerInterface;

class CustomerGroupHandler
{
    public function __construct(
        private readonly Session $session,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @return bool
     */
    public function userIsGuest(): bool
    {
        try {
            return 0 === (int) $this->session->getQuote()->getCustomerId();
        } catch (\Exception $exception) {
            $this->logger->error('Could not detect if user is guest: ' . $exception->getMessage());
        }

        return true;
    }
}
