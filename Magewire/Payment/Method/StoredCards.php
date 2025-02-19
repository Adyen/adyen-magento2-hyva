<?php

declare(strict_types=1);

namespace Adyen\Hyva\Magewire\Payment\Method;

use Adyen\Hyva\Api\ProcessingMetadataInterface;
use Adyen\Hyva\Model\Component\Payment\Context;
use Adyen\Hyva\Model\CreditCard\InstallmentsManager;
use Exception;
use Hyva\Checkout\Model\Magewire\Component\Evaluation\EvaluationResult;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Magento\Quote\Api\Data\PaymentExtensionFactory;

class StoredCards extends AdyenPaymentComponent
{
    const METHOD_STORED_CC = 'adyen_cc_vault';

    public function __construct(
        private readonly Context $context,
        private readonly InstallmentsManager $installmentsManager,
        private readonly PaymentExtensionFactory $paymentExtensionFactory

    ) {
        parent::__construct(
            $this->context,
            $this->paymentExtensionFactory
        );
    }

    /**
     * @return string
     */
    public function getMethodCode(): string
    {
        return CreditCard::METHOD_CC;
    }

    /**
     * {@inheritDoc}
     */
    public function evaluateCompletion(EvaluationResultFactory $resultFactory): EvaluationResult
    {
        return $resultFactory->createSuccess();
    }

    /**
     * @return string
     */
    public function getFormattedInstallments(): string
    {
        return $this->installmentsManager->getFormattedInstallments();
    }

    /**
     * @inheritDoc
     */
    public function placeOrder(array $data): void
    {
        $this->handleSessionVariables($data);

        try {
            $quotePayment = $this->session->getQuote()->getPayment();
            $quotePayment?->setMethod(self::METHOD_STORED_CC);
        } catch (Exception $e) {
            $this->logger->error('Could not prepare quote payment for Stored Cards: ' . $e->getMessage());
        }

        parent::placeOrder($data);
    }

    /**
     * @param array $data
     */
    private function handleSessionVariables(array $data)
    {
        //Clean the public hash value
        $this->session->setStoredCardPublicHash(null);

        //Set with input data if applicable
        if (isset($data[ProcessingMetadataInterface::POST_KEY_PUBLIC_HASH])) {
            $this->session->setStoredCardPublicHash($data[ProcessingMetadataInterface::POST_KEY_PUBLIC_HASH]);
        }
    }
}
