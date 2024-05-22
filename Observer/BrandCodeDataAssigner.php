<?php

declare(strict_types=1);

namespace Adyen\Hyva\Observer;

use Adyen\Payment\Observer\AdyenPaymentMethodDataAssignObserver;
use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;

/**
 * This class will be deprecated after implementing ECP-9078.
 */
class BrandCodeDataAssigner extends AbstractDataAssignObserver
{
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

        $paymentMethod = $paymentInfo->getMethodInstance()->getCode();
        $brandCode = str_starts_with($paymentMethod, 'adyen_') ?
            str_replace('adyen_', '', $paymentMethod) :
            null;

        if (isset($brandCode)) {
            $paymentInfo->setAdditionalInformation(AdyenPaymentMethodDataAssignObserver::BRAND_CODE, $brandCode);
        }
    }
}
