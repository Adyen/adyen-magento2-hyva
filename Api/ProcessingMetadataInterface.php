<?php

namespace Adyen\Hyva\Api;

interface ProcessingMetadataInterface
{
    const METHOD_ADYEN_PREFIX = 'adyen_';
    const METHOD_CC = 'adyen_cc';
    const METHOD_SAVED_CC = 'adyen_cc_vault';
    const METHOD_GOOGLE_PAY = 'adyen_googlepay';
    const METHOD_APPLE_PAY = 'adyen_applepay';
    const METHOD_PAYPAL = 'adyen_paypal';
    const POST_KEY_STATE_DATA = 'stateData';
    const PUBLIC_HASH = 'public_hash';
    const POST_KEY_PUBLIC_HASH = 'publicHash';
    const POST_KEY_ORDER_ID = 'order_id';
    const VAULT_LAYOUT_PREFIX = 'adyen_vault_';
    const BLOCK_PROPERTY_MAGEWIRE = 'magewire';
    const BLOCK_PROPERTY_STORED_CARD = 'storedCard';
}
