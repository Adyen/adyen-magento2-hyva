<?php

declare(strict_types=1);

namespace Adyen\Hyva\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Model\Quote\Payment;
use Psr\Log\LoggerInterface;

class InstallmentsDataAssigner extends AbstractDataAssignObserver
{
    public function __construct(
        private Session $checkoutSession,
        private LoggerInterface $logger
    ) {
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

        if ($this->checkoutSession->getNumberOfInstallments()) {
            try {
                $paymentModel->setAdditionalInformation(
                    [
                        'number_od_installments' => $this->checkoutSession->getNumberOfInstallments(),
                        'cc_type' => $this->checkoutSession->getCcType()
                    ]
                );
            } catch (\Exception $exception) {
                $this->logger->error('Could not add additional installments information to the payment model: ' . $exception->getMessage());
            }
        }
    }
}
