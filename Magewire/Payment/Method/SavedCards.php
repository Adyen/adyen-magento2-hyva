<?php

declare(strict_types=1);

namespace Adyen\Hyva\Magewire\Payment\Method;

use Adyen\Hyva\Api\ProcessingMetadataInterface;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;

class SavedCards extends AdyenPaymentComponent
{
    /**
     * @return string
     */
    public function getMethodCode(): string
    {
        return ProcessingMetadataInterface::METHOD_SAVED_CC;
    }

    /**
     * {@inheritDoc}
     */
    public function evaluateCompletion(EvaluationResultFactory $resultFactory): EvaluationResultInterface
    {
        return $resultFactory->createSuccess();
    }

    /**
     * @inheritDoc
     */
    public function placeOrder(array $data): void
    {
        $this->handleSessionVariables($data);
        $quotePayment = $this->session->getQuote()->getPayment();
        $quotePayment->setMethod($this->getMethodCode());

        parent::placeOrder($data);
    }

    /**
     * @param array $data
     */
    private function handleSessionVariables(array $data)
    {
        //Clean the public hash value
        $this->session->setSavedCardPublicHash(null);

        //Set with input data if applicable
        if (isset($data[ProcessingMetadataInterface::POST_KEY_PUBLIC_HASH])) {
            $this->session->setSavedCardPublicHash($data[ProcessingMetadataInterface::POST_KEY_PUBLIC_HASH]);
        }
    }
}
