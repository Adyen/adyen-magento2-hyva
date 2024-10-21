<?php

declare(strict_types=1);

namespace Adyen\Hyva\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
class CompositeConfigProvider implements ConfigProviderInterface
{
    private $configProviders;

    public function __construct(
        array $configProviders
    ) {
        $this->configProviders = $configProviders;
    }

    public function getConfig()
    {
        $config = [];
        foreach ($this->configProviders as $configProvider) {
            $config = array_merge_recursive($config, $configProvider->getConfig());
        }
        return $config;
    }
}
