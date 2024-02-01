<?php

namespace Adyen\Hyva\Plugin;

use Adyen\Hyva\Model\PaymentMethod\PaymentMethods;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface as Subject;
use Adyen\Hyva\Api\ProcessingMetadataInterface;
use Adyen\Hyva\Model\CreditCard\SavedCardsManager;
use Adyen\Hyva\Model\MethodList;

class InitMethods
{
    private CartRepositoryInterface $cartRepository;
    private MethodList $methodList;
    private PaymentMethods $paymentMethods;
    private SavedCardsManager $savedCardsManager;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        MethodList $methodList,
        PaymentMethods $paymentMethods,
        SavedCardsManager $savedCardsManager
    ) {
        $this->cartRepository = $cartRepository;
        $this->methodList = $methodList;
        $this->paymentMethods = $paymentMethods;
        $this->savedCardsManager = $savedCardsManager;
    }

    /**
     * @param Subject $subject
     * @param array $list
     * @param $quoteId
     * @return array
     * @throws LocalizedException
     */
    public function afterGetList(Subject $subject, array $list, $quoteId): array
    {
        try {
            /**
             * Filter out methods that are Adyen Based, but are not supported in Hyva checkout
             */
            foreach ($list as $key => $method) {
                if ($this->isMethodAdyenBased($method) && !$this->isMethodHyvaSupported($method)) {
                    unset($list[$key]);
                }
            }

            /**
             * Filter out methods that are Adyen Based, but are not supported by Adyen configuration
             */
            $configuredKeys = $this->collectConfiguredKeys((int) $quoteId);

            foreach ($list as $key => $method) {
                if ($this->isMethodAdyenBased($method) && !$this->isMethodAvailable($method, $configuredKeys)) {
                    unset($list[$key]);
                }
            }

            /**
             * Remove Apple Pay if the device would not support the method
             */
            $list = $this->handleApplePay($list);

            /**
             * Handle Stored Cards
             */
            $storedCards = $this->savedCardsManager->getStoredCards();

            if (empty($storedCards)) {
                foreach ($list as $key => $method) {
                    if ($method->getCode() == ProcessingMetadataInterface::METHOD_SAVED_CC) {
                        unset($list[$key]);
                    }
                }
            }

            return $list;
        } catch (Exception $exception) {
            throw new LocalizedException(__('Cannot find active quote'));
        }

        return [];
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
     * @return bool
     */
    private function isMethodHyvaSupported($method): bool
    {
        if (in_array($method->getCode(), $this->methodList->collectAvailableMethods())) {
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
        if ($methodCode == ProcessingMetadataInterface::METHOD_CC) {
            return 'card';
        }

        return substr(
            $methodCode,
            strpos($methodCode, ProcessingMetadataInterface::METHOD_ADYEN_PREFIX) + strlen(ProcessingMetadataInterface::METHOD_ADYEN_PREFIX)
        );
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
     * @param array $list
     * @return array
     */
    private function handleApplePay(array $list): array
    {
        // TODO: Verify detection of Safari browser
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        if (preg_match('/safari/i', $user_agent)) {
            return $list;
        }

        foreach ($list as $key => $method) {
            if ($method->getCode() == ProcessingMetadataInterface::METHOD_APPLE_PAY) {
                unset($list[$key]);
            }
        }

        return $list;
    }
}
