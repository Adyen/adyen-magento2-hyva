<?php

namespace Adyen\Hyva\Model\PaymentMethod\Filter;

use Adyen\Hyva\Api\ProcessingMetadataInterface;
use Adyen\Hyva\Magewire\Payment\Method\CreditCard;
use Adyen\Hyva\Model\PaymentMethod\PaymentMethods;

/**
 * Filter out methods that are Adyen Based, but are not supported by Adyen configuration
 */
class AdyenConfigured implements FilterInterface
{
    public function __construct(
        private readonly PaymentMethods $paymentMethods
    ) {
    }
    /**
     * {@inheritDoc}
     */
    public function execute(int $quoteId, array $list): array
    {
        $configuredKeys = $this->collectConfiguredKeys($quoteId);

        foreach ($list as $key => $method) {
            if ($this->isMethodAdyenBased($method) && !$this->isMethodAvailable($method, $configuredKeys)) {
                unset($list[$key]);
            }
        }

        return $list;
    }

    /**
     * @param int $quoteId
     * @return array
     */
    private function collectConfiguredKeys(int $quoteId): array
    {
        $paymentMethodsConfiguration = json_decode($this->paymentMethods->getData($quoteId), true);

        if (!isset($paymentMethodsConfiguration['paymentMethodsExtraDetails'])) {
            return [];
        }

        return array_keys($paymentMethodsConfiguration['paymentMethodsExtraDetails']);
    }

    /**
     * @param $method
     * @return bool
     */
    private function isMethodAdyenBased($method): bool
    {
        if (str_starts_with($method->getCode(), ProcessingMetadataInterface::METHOD_ADYEN_PREFIX)) {
            return true;
        }

        return false;
    }

    /**
     * @param $method
     * @param $configuredKeys
     * @return bool
     */
    private function isMethodAvailable($method, $configuredKeys): bool
    {
        $methodCode = $this->collectMethodCodeWithoutPrefix($method->getCode());

        return in_array($methodCode, $configuredKeys);
    }

    private function collectMethodCodeWithoutPrefix($methodCode): string
    {
        if ($methodCode == CreditCard::METHOD_CC) {
            return 'scheme';
        }

        return substr(
            $methodCode,
            strpos($methodCode, ProcessingMetadataInterface::METHOD_ADYEN_PREFIX) + strlen(ProcessingMetadataInterface::METHOD_ADYEN_PREFIX)
        );
    }
}
