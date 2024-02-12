<?php

namespace Adyen\Hyva\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Model\Quote\Payment;

class InstallmentsDataAssigner extends AbstractDataAssignObserver
{
    private Session $checkoutSession;

    public function __construct(
        Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
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
            $paymentModel->setAdditionalInformation(
                [
                    'number_od_installments' => $this->checkoutSession->getNumberOfInstallments(),
                    'cc_type' => $this->checkoutSession->getCcType()
                ]
            );
        }
    }
}
