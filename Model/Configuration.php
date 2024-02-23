<?php

declare(strict_types=1);

namespace Adyen\Hyva\Model;

use Magento\Checkout\Model\CompositeConfigProvider;

class Configuration
{
    private \stdClass $paymentConfiguration;

    public function __construct(
        CompositeConfigProvider $configProvider
    ) {
        if (isset($configProvider->getConfig()['payment']) &&
            $paymentConfiguration = json_decode(json_encode($configProvider->getConfig()['payment']))
        ) {
            $this->paymentConfiguration = $paymentConfiguration;
        }
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function getValue(string $path): mixed
    {
        $result = $this->paymentConfiguration;
        $pathParts = explode('/', $path);

        if (is_array($pathParts)) {
            foreach ($pathParts as $part) {
                if (property_exists($result, $part)) {
                    $result = $result->$part;
                } else {
                    return null;
                }
            }

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

        return $this->getValue('adyenCc/isCardRecurringEnabled');
    }
}
