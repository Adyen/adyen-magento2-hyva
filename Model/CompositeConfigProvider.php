<?php

declare(strict_types=1);

namespace Adyen\Hyva\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
class CompositeConfigProvider implements ConfigProviderInterface
{
    /**
     * @param array $configProviders
     */
    public function __construct(
        private readonly array $configProviders
    ) { }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        $config = [];

        foreach ($this->configProviders as $configProvider) {
            $config = array_merge_recursive($config, $configProvider->getConfig());
        }

        return $config;
    }
}
