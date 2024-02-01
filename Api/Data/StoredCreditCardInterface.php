<?php

namespace Adyen\Hyva\Api\Data;

interface StoredCreditCardInterface
{
    const GATEWAY_TOKEN = 'gateway_token';
    const PUBLIC_HASH = 'public_hash';
    const TYPE = 'type';
    const MASKED_CC = 'masked_cc';
    const EXPIRY_MONTH = 'expiry_month';
    const EXPIRY_YEAR = 'expiry_year';
    const LAYOUT_ID = 'layout_id';

    /**
     * @param string $token
     * @return $this
     */
    public function setGatewayToken(string $token): self;

    /**
     * @param string $publicHash
     * @return $this
     */
    public function setPublicHash(string $publicHash): self;

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type): self;

    /**
     * @param string $maskedCc
     * @return $this
     */
    public function setMaskedCc(string $maskedCc): self;

    /**
     * @param string $expiryMonth
     * @return $this
     */
    public function setExpiryMonth(string $expiryMonth): self;

    /**
     * @param string $expiryYear
     * @return $this
     */
    public function setExpiryYear(string $expiryYear): self;

    /**
     * @param string $layoutId
     * @return $this
     */
    public function setLayoutId(string $layoutId): self;

    /**
     * @return string|null
     */
    public function getGatewayToken(): ?string;

    /**
     * @return string|null
     */
    public function getPublicHash(): ?string;

    /**
     * @return string|null
     */
    public function getType(): ?string;

    /**
     * @return string|null
     */
    public function getMaskedCc(): ?string;

    /**
     * @return string|null
     */
    public function getExpiryMonth(): ?string;

    /**
     * @return string|null
     */
    public function getExpiryYear(): ?string;

    /**
     * @return string
     */
    public function getLayoutId(): string;

    /**
     * @return string
     */
    public function getPublicLabel(): string;
}
