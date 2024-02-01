<?php

namespace Adyen\Hyva\Api\Data;

use Magewirephp\Magewire\Component;

interface MagewireComponentInterface
{
    const NAME = 'name';
    const MAGEWIRE = 'magewire';
    const STORED_CARD = 'storedCard';
    const TEMPLATE = 'template';

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self;

    /**
     * @param Component $component
     * @return $this
     */
    public function setMagewire(Component $component): self;

    /**
     * @param StoredCreditCardInterface $storedCard
     * @return $this
     */
    public function setStoredCard(StoredCreditCardInterface $storedCard): self;

    /**
     * @param string $template
     * @return $this
     */
    public function setTemplate(string $template): self;

    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @return Component|null
     */
    public function getMagewire(): ?Component;

    /**
     * @return StoredCreditCardInterface|null
     */
    public function getStoredCard(): ?StoredCreditCardInterface;

    /**
     * @return string|null
     */
    public function getTemplate(): ?string;
}
