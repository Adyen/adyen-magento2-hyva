<?php

declare(strict_types=1);

namespace Adyen\Hyva\Model\Data;

use Magento\Framework\Model\AbstractModel;
use Adyen\Hyva\Api\Data\StoredCreditCardInterface;

class StoredCreditCard extends AbstractModel implements StoredCreditCardInterface
{
    /**
     * @inheritDoc
     */
    public function setGatewayToken(string $token): StoredCreditCardInterface
    {
        return $this->setData(self::GATEWAY_TOKEN, $token);
    }

    /**
     * @inheritDoc
     */
    public function setPublicHash(string $publicHash): StoredCreditCardInterface
    {
        return $this->setData(self::PUBLIC_HASH, $publicHash);
    }

    /**
     * @inheritDoc
     */
    public function setType(string $type): StoredCreditCardInterface
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * @inheritDoc
     */
    public function setMaskedCc(string $maskedCc): StoredCreditCardInterface
    {
        return $this->setData(self::MASKED_CC, $maskedCc);
    }

    /**
     * @inheritDoc
     */
    public function setExpiryMonth(string $expiryMonth): StoredCreditCardInterface
    {
        return $this->setData(self::EXPIRY_MONTH, $expiryMonth);
    }

    /**
     * @inheritDoc
     */
    public function setExpiryYear(string $expiryYear): StoredCreditCardInterface
    {
        return $this->setData(self::EXPIRY_YEAR, $expiryYear);
    }

    /**
     * @inheritDoc
     */
    public function setLayoutId(string $layoutId): StoredCreditCardInterface
    {
        return $this->setData(self::LAYOUT_ID, $layoutId);
    }

    /**
     * @inheritDoc
     */
    public function getGatewayToken(): ?string
    {
        return $this->getData(self::GATEWAY_TOKEN);
    }

    /**
     * @inheritDoc
     */
    public function getPublicHash(): ?string
    {
        return $this->getData(self::PUBLIC_HASH);
    }

    /**
     * @inheritDoc
     */
    public function getType(): ?string
    {
        return $this->getData(self::TYPE);
    }

    /**
     * @inheritDoc
     */
    public function getMaskedCc(): ?string
    {
        return $this->getData(self::MASKED_CC);
    }

    /**
     * @inheritDoc
     */
    public function getExpiryMonth(): ?string
    {
        return $this->getData(self::EXPIRY_MONTH);
    }

    /**
     * @inheritDoc
     */
    public function getExpiryYear(): ?string
    {
        return $this->getData(self::EXPIRY_YEAR);
    }

    /**
     * @inheritDoc
     */
    public function getLayoutId(): string
    {
        return $this->getData(self::LAYOUT_ID);
    }

    /**
     * @inheritDoc
     */
    public function getPublicLabel(): string
    {
        return $this->getType() . ' ' . __('ending with') . ' ' . $this->getMaskedCc();
    }
}
