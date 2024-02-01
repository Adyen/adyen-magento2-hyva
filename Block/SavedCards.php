<?php

namespace Adyen\Hyva\Block;

use Adyen\Hyva\Api\Data\StoredCreditCardInterface;
use Adyen\Hyva\Api\ProcessingMetadataInterface;
use Adyen\Hyva\Magewire\Payment\Method\SavedCards as SavedCardsWire;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magewirephp\Magewire\Component;

class SavedCards extends Template
{
    /**
     * @return Component|null
     */
    public function getHyvaPaymentMethodsMagewire(): ?Component
    {
        try {
            $paymentMethodsBlock = $this->getLayout()->getBlock('checkout.payment.methods');

            if ($paymentMethodsBlock
                && $paymentMethodsBlock->getData(ProcessingMetadataInterface::BLOCK_PROPERTY_MAGEWIRE)
            ) {
                return $paymentMethodsBlock->getData(ProcessingMetadataInterface::BLOCK_PROPERTY_MAGEWIRE);
            }
        } catch (\Exception $exception) {
            return null;
        }

        return null;
    }

    /**
     * @param StoredCreditCardInterface $storedCreditCard
     * @return Template|null
     */
    public function getNewVaultBlock(StoredCreditCardInterface $storedCreditCard): ?Template
    {
        try {
            return $this->getLayout()->createBlock(Template::class)
                ->setNameInLayout($storedCreditCard->getLayoutId())
                ->setData(ProcessingMetadataInterface::BLOCK_PROPERTY_STORED_CARD, $storedCreditCard)
                ->setData(ProcessingMetadataInterface::BLOCK_PROPERTY_MAGEWIRE, $this->getNewMagewireInstance())
                ->setTemplate('Adyen_Hyva::payment/method-renderer/adyen-cc-vault-method.phtml');
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * @return Component
     */
    private function getNewMagewireInstance(): Component
    {
        return ObjectManager::getInstance()->create(SavedCardsWire::class);
    }
}
