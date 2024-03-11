<?php

declare(strict_types=1);

namespace Adyen\Hyva\Observer;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Model\Quote\Payment;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Psr\Log\LoggerInterface;

class PaymentTokenAssigner extends AbstractDataAssignObserver
{
    public function __construct(
        private Session $checkoutSession,
        private PaymentTokenManagementInterface $paymentTokenManagement,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer): void
    {
        try {
            $paymentInfo = $this->readPaymentModelArgument($observer);
        } catch (\LogicException $exception) {
            return;
        }

        try {
            $tokenPublicHash = $this->checkoutSession->getStoredCardPublicHash();

            if (!$tokenPublicHash) {
                return;
            }

            $quote = $paymentInfo->getQuote();
            $customerId = (int) $quote->getCustomer()->getId();
            $paymentToken = $this->paymentTokenManagement->getByPublicHash($tokenPublicHash, $customerId);

            if (!$customerId || $paymentToken === null) {
                return;
            }

            $this->updateAdditionalInformation($paymentInfo, $customerId, $tokenPublicHash);
        } catch (Exception $e) {
            $this->logger->error('Could not add payment additional information: ' . $e->getMessage());
        }
    }

    /**
     * @param Payment $paymentModel
     * @param int $customerId
     * @param string $tokenPublicHash
     * @return void
     * @throws LocalizedException
     */
    private function updateAdditionalInformation(Payment $paymentModel, int $customerId, string $tokenPublicHash): void
    {
        $additionalInformation = $paymentModel->getAdditionalInformation();

        if (is_array($additionalInformation)) {
            $additionalInformation[PaymentTokenInterface::CUSTOMER_ID] = $customerId;
            $additionalInformation[PaymentTokenInterface::PUBLIC_HASH] = $tokenPublicHash;

            $paymentModel->setAdditionalInformation($additionalInformation);
        }
    }
}
