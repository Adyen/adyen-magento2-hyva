<?php

declare(strict_types=1);

namespace Adyen\Hyva\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
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

        if (!$this->checkoutSession->getNumberOfInstallments() || !$this->checkoutSession->getCcType()) {
            return;
        }

        try {
            $this->updateAdditionalInformation(
                $paymentModel,
                (int) $this->checkoutSession->getNumberOfInstallments(),
                (string) $this->checkoutSession->getCcType()
            );
        } catch (\Exception $e) {
            $this->logger->error('Could not add additional installments information to the payment model: ' . $e->getMessage());
        }
    }

    /**
     * @param Payment $paymentModel
     * @param int $numberOfInstallments
     * @param string $ccType
     * @return void
     * @throws LocalizedException
     */
    private function updateAdditionalInformation(Payment $paymentModel, int $numberOfInstallments, string $ccType): void
    {
        $additionalInformation = $paymentModel->getAdditionalInformation();

        if (is_array($additionalInformation)) {
            $additionalInformation['number_od_installments'] = $numberOfInstallments;
            $additionalInformation['cc_type'] = $ccType;

            $paymentModel->setAdditionalInformation($additionalInformation);
        }
    }
}
