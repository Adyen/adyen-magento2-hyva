<?php

declare(strict_types=1);

namespace Adyen\Hyva\Magewire\Checkout;

use Adyen\Payment\Api\AdyenDonationsInterface;
use Adyen\Payment\Api\GuestAdyenDonationsInterface;
use Adyen\Payment\Helper\Config;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magewirephp\Magewire\Component;
use Psr\Log\LoggerInterface;

class Success extends Component
{
    public bool $donationStatus = false;

    public function __construct(
        private Session $session,
        private AdyenDonationsInterface $adyenDonations,
        private GuestAdyenDonationsInterface $guestAdyenDonations,
        private OrderFactory $orderFactory,
        private Config $helperConfig,
        private StoreManagerInterface $storeManager,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @return bool
     */
    public function userIsGuest(): string
    {
        try {
            $customerId = $this->session->getQuote()->getCustomerId();

            if ($customerId) {
                return "customer";
            }
        } catch (Exception $exception) {
            return "guest";
        }

        return "guest";
    }

    public function showAdyenGiving(): bool
    {
        return $this->adyenGivingEnabled() && $this->hasDonationToken();
    }

    private function adyenGivingEnabled(): bool
    {
        return (bool) $this->helperConfig->adyenGivingEnabled($this->storeManager->getStore()->getId());
    }

    private function hasDonationToken(): bool
    {
        return $this->getDonationToken() && 'null' !== $this->getDonationToken();
    }

    private function getDonationToken()
    {
        return json_encode($this->getOrder()->getPayment()->getAdditionalInformation('donationToken'));
    }

    private function getOrder()
    {
        return $this->orderFactory->create()->load($this->session->getLastOrderId());
    }

    /**
     * @return string
     */
    public function getStateData(): string
    {
        return json_encode($this->session->getStateData()) ?? '{}';
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
