<?php

declare(strict_types=1);

namespace Adyen\Hyva\Model\CreditCard;

use Adyen\Hyva\Api\Data\MagewireComponentInterface;
use Adyen\Hyva\Api\Data\MagewireComponentInterfaceFactory;
use Adyen\Hyva\Magewire\Payment\Method\StoredCards;
use Adyen\Payment\Helper\Vault as AdyenVaultHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Adyen\Hyva\Api\Data\StoredCreditCardInterface;
use Adyen\Hyva\Api\Data\StoredCreditCardInterfaceFactory;
use Adyen\Hyva\Api\ProcessingMetadataInterface;
use Psr\Log\LoggerInterface;

class StoredCardsManager
{
    private array $storedCards = [];
    private bool $storedCardsLoaded = false;

    public function __construct(
        private PaymentTokenManagementInterface $paymentTokenManagement,
        private Session $customerSession,
        private CheckoutSession $checkoutSession,
        private AdyenVaultHelper $adyenVaultHelper,
        private StoreManagerInterface $storeManager,
        private StoredCreditCardInterfaceFactory $storedCreditCardFactory,
        private MagewireComponentInterfaceFactory $magewireComponentInterfaceFactory,
        private BrandsManager $brandsManager,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @param string $layoutId
     * @return StoredCreditCardInterface|null
     */
    public function getStoredCard(string $layoutId): ?StoredCreditCardInterface
    {
        if (!$this->storedCardsLoaded) {
            $this->getStoredCards();
        }

        /** @var StoredCreditCardInterface $storedCard */
        foreach ($this->storedCards as $storedCard) {
            if ($storedCard->getLayoutId() == $layoutId) {
                return $storedCard;
            }
        }

        return null;
    }

    /**
     * @param string $name
     * @return MagewireComponentInterface|null
     */
    public function getMagewireComponent(string $name): ?MagewireComponentInterface
    {
        /** @var StoredCreditCardInterface $storedCard */

        if (str_starts_with($name, ProcessingMetadataInterface::VAULT_LAYOUT_PREFIX)
            && $storedCard = $this->getStoredCard($name)
        ) {
            $magewireComponent = $this->magewireComponentInterfaceFactory->create();
            $magewireComponent->setName($name)
                ->setMagewire(ObjectManager::getInstance()->create(StoredCards::class))
                ->setTemplate('Adyen_Hyva::payment/method-renderer/adyen-cc-vault-method.phtml')
                ->setStoredCard($storedCard);

            return $magewireComponent;
        }

        return null;
    }

    /**
     * @return array
     */
    public function getStoredCards(): array
    {
        if ($this->storedCardsLoaded) {
            return $this->storedCards;
        }

        $this->storedCardsLoaded = true;

        try {
            $storeId = (int) $this->storeManager->getStore()->getId();
            $customerId = (int) $this->customerSession->getCustomerId();
            $vaultData = $this->paymentTokenManagement->getListByCustomerId($customerId);
            $counter = 1;

            /** @var PaymentTokenInterface $vaultToken */
            foreach ($vaultData as $vaultToken) {
                if ($this->adyenVaultHelper->getPaymentMethodRecurringActive($vaultToken->getPaymentMethodCode(), $storeId)
                    && str_starts_with((string) $vaultToken->getPaymentMethodCode(), ProcessingMetadataInterface::METHOD_ADYEN_PREFIX)
                ) {
                    if ($storedCardDataObject = $this->getStoredCardDataObject($vaultToken, $counter)) {
                        $this->storedCards[] = $storedCardDataObject;
                        $counter++;
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->logger->error('Error collecting stored cards: ' . $exception->getMessage());
        }

        return $this->storedCards;
    }

    /**
     * @param PaymentTokenInterface $vaultToken
     * @param int $counter
     * @return StoredCreditCardInterface|null
     */
    private function getStoredCardDataObject(PaymentTokenInterface $vaultToken, int $counter): ?StoredCreditCardInterface
    {
        if ($vaultToken->getIsActive()
            && $vaultToken->getIsVisible()
            && $vaultToken->getCustomerId() == (int) $this->customerSession->getCustomerId()
        ) {
            $vaultTokenDetails = json_decode($vaultToken->getTokenDetails(), true);

            if (!isset($vaultTokenDetails['type'])
                || !isset($vaultTokenDetails['maskedCC'])
                || !isset($vaultTokenDetails['expirationDate'])
                || !isset($vaultTokenDetails['tokenType'])
                || $vaultTokenDetails['tokenType'] != AdyenVaultHelper::CARD_ON_FILE
            ) {
                return null;
            }

            if(!in_array($vaultTokenDetails['type'], $this->brandsManager->getBrandsAsArray())) {
                return null;
            }

            $expirationDate = $vaultTokenDetails['expirationDate'];
            list($expirationMonth, $expirationYear) = explode('/', $expirationDate);

            $storedCreditCard = $this->storedCreditCardFactory->create();

            $storedCreditCard->setGatewayToken($vaultToken->getGatewayToken())
                ->setPublicHash($vaultToken->getPublicHash())
                ->setType($vaultTokenDetails['type'])
                ->setMaskedCc($vaultTokenDetails['maskedCC'])
                ->setExpiryMonth($expirationMonth)
                ->setExpiryYear(substr($expirationYear, -2))
                ->setLayoutId(ProcessingMetadataInterface::VAULT_LAYOUT_PREFIX . $counter);

            return $storedCreditCard;
        }

        return null;
    }
}
