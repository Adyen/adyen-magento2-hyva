<?php

declare(strict_types=1);

namespace Adyen\Hyva\Api;

interface ProcessingMetadataInterface
{
    const METHOD_ADYEN_PREFIX = 'adyen_';
    const POST_KEY_STATE_DATA = 'stateData';
    const PUBLIC_HASH = 'public_hash';
    const POST_KEY_PUBLIC_HASH = 'publicHash';
    const POST_KEY_ORDER_ID = 'order_id';
    const POST_KEY_NUMBER_OF_INSTALLMENTS = 'installments';
    const POST_KEY_CC_TYPE = 'ccType';
    const VAULT_LAYOUT_PREFIX = 'adyen_vault_';
    const BLOCK_PROPERTY_MAGEWIRE = 'magewire';
    const BLOCK_PROPERTY_STORED_CARD = 'storedCard';
}
