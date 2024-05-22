<?php

declare(strict_types=1);

namespace Adyen\Hyva\Plugin;

use Adyen\Hyva\Model\PaymentMethod\Filter\FilterInterface;
use Exception;
use Magento\Quote\Api\PaymentMethodManagementInterface as Subject;
use Psr\Log\LoggerInterface;

class InitMethods
{
    public function __construct(
        private readonly FilterInterface $paymentMethodFilter,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param Subject $subject
     * @param array $list
     * @param $quoteId
     * @return array
     */
    public function afterGetList(Subject $subject, array $list, $quoteId): array
    {
        try {
            $list = $this->paymentMethodFilter->execute((int) $quoteId, $list);
        } catch (Exception $exception) {
            $this->logger->error('Error during filtering available methods: ' . $exception->getMessage());
        }

        return $list;
    }
}
