<?php

namespace Adyen\Hyva\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Model\Quote\Payment;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;

class PaymentTokenAssigner extends AbstractDataAssignObserver
{
    private Session $checkoutSession;
    private PaymentTokenManagementInterface $paymentTokenManagement;

    public function __construct(
        Session $checkoutSession,
        PaymentTokenManagementInterface $paymentTokenManagement
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->paymentTokenManagement = $paymentTokenManagement;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
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
    }
}
