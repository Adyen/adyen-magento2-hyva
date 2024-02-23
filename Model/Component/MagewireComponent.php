<?php

declare(strict_types=1);

namespace Adyen\Hyva\Model\Component;

use Adyen\Hyva\Api\Data\MagewireComponentInterface;
use Adyen\Hyva\Api\Data\StoredCreditCardInterface;
use Magento\Framework\Model\AbstractModel;
use Magewirephp\Magewire\Component;

class MagewireComponent extends AbstractModel implements MagewireComponentInterface
{
    /**
     * @inheritDoc
     */
    public function setName(string $name): MagewireComponentInterface
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function setMagewire(Component $component): MagewireComponentInterface
    {
        return $this->setData(self::MAGEWIRE, $component);
    }

    /**
     * @inheritDoc
     */
    public function setStoredCard(StoredCreditCardInterface $storedCard): MagewireComponentInterface
    {
        return $this->setData(self::STORED_CARD, $storedCard);
    }

    /**
     * @inheritDoc
     */
    public function setTemplate(string $template): MagewireComponentInterface
    {
        return $this->setData(self::TEMPLATE, $template);
    }

    /**
     * @inheritDoc
     */
    public function getName(): ?string
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function getMagewire(): ?Component
    {
        return $this->getData(self::MAGEWIRE);
    }

    /**
     * @inheritDoc
     */
    public function getTemplate(): ?string
    {
        return $this->getData(self::TEMPLATE);
    }

    /**
     * @inheritDoc
     */
    public function getStoredCard(): ?StoredCreditCardInterface
    {
        return $this->getData(self::STORED_CARD);
    }
}
