<?php

declare(strict_types=1);

namespace Adyen\Hyva\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
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
    public function execute(Observer $observer)
    {
        try {
            /** @var Payment $paymentModel */
            $paymentModel = $this->readPaymentModelArgument($observer);

            if (!$paymentModel instanceof Payment) {
                return;
            }

            $quote = $paymentModel->getQuote();
            $tokenPublicHash = $this->checkoutSession->getSavedCardPublicHash();

            if (!$tokenPublicHash) {
                return;
            }

            $customerId = (int) $quote->getCustomer()->getId();
            $paymentToken = $this->paymentTokenManagement->getByPublicHash($tokenPublicHash, $customerId);

            if ($paymentToken === null) {
                return;
            }

            $paymentModel->setAdditionalInformation(
                [
                    PaymentTokenInterface::CUSTOMER_ID => $customerId,
                    PaymentTokenInterface::PUBLIC_HASH => $tokenPublicHash
                ]
            );
        } catch (\Exception $exception) {
            $this->logger->error('Could not add additional public hash information to the payment model: ' . $exception->getMessage());
        }
    }
}
