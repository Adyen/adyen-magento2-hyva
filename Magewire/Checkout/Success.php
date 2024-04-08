<?php

declare(strict_types=1);

namespace Adyen\Hyva\Magewire\Checkout;

use Adyen\Hyva\Model\Customer\CustomerGroupHandler;
use Adyen\Payment\Api\AdyenDonationsInterface;
use Adyen\Payment\Api\GuestAdyenDonationsInterface;
use Magewirephp\Magewire\Component;
use Psr\Log\LoggerInterface;

class Success extends Component
{
    public bool $donationStatus = false;

    public function __construct(
        private AdyenDonationsInterface $adyenDonations,
        private GuestAdyenDonationsInterface $guestAdyenDonations,
        private CustomerGroupHandler $customerGroupHandler,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @return bool
     */
    public function userIsGuest(): bool
    {
        return $this->customerGroupHandler->userIsGuest();
    }

    /**
     * @param int $orderId
     * @param array $payload
     */
    public function donate(int $orderId, array $payload)
    {
        try {
            $this->adyenDonations->donate($orderId, json_encode($payload));
            $this->donationStatus = true;
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            $this->donationStatus = false;
        }
    }

    /**
     * @param string $maskedCartId
     * @param array $payload
     */
    public function donateGuest(string $maskedCartId, array $payload)
    {
        try {
            $this->guestAdyenDonations->donate($maskedCartId, json_encode($payload));
            $this->donationStatus = true;
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            $this->donationStatus = false;
        }
    }
}
