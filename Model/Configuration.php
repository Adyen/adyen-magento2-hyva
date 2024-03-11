<?php

declare(strict_types=1);

namespace Adyen\Hyva\Model;

use Exception;
use Magento\Checkout\Model\CompositeConfigProvider;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Psr\Log\LoggerInterface;

class Configuration
{
    private ?DataObject $paymentConfiguration = null;

    public function __construct(
        CompositeConfigProvider $configProvider,
        DataObjectFactory $dataObjectFactory,
        LoggerInterface $logger
    ) {
        try {
            if (isset($configProvider->getConfig()['payment'])) {
                $this->paymentConfiguration = $dataObjectFactory->create(
                    ['data' => $configProvider->getConfig()['payment']]
                );
            }
        } catch (Exception $exception) {
            $this->paymentConfiguration = null;
            $logger->error('Could not instantiate payment config: ' . $exception->getMessage());
        }
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function getValue(string $path): mixed
    {
        if ($this->paymentConfiguration && $result = $this->paymentConfiguration->getData($path)) {
            return $result;
        }

        return null;
    }

    public function getJsonValue(string $path): string
    {
        return json_encode($this->getValue($path));
    }

    /**
     * @return bool
     */
    public function isCCEnableStoreDetails(bool $userIsGuest): bool
    {
        if ($userIsGuest) {
            return false;
        }

        return (bool) $this->getValue('adyenCc/isCardRecurringEnabled');
    }
}
