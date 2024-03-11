<?php

declare(strict_types=1);

namespace Adyen\Hyva\Model\PaymentMethod;

use Adyen\Payment\Helper\PaymentMethods as AdyenPaymentMethods;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

class PaymentMethods
{
    private ?string $data = null;

    public function __construct(
        private readonly AdyenPaymentMethods $adyenPaymentMethods,
        private readonly Json $jsonSerializer,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param int $quoteId
     * @return string
     */
    public function getData(int $quoteId): string
     {
         if ($this->data === null) {
             try {
                 $paymentMethods = $this->jsonSerializer->unserialize(
                     $this->adyenPaymentMethods->getPaymentMethods($quoteId)
                 );
                 unset($paymentMethods['paymentMethodsResponse']['storedPaymentMethods']);

                 $this->data = $this->jsonSerializer->serialize($paymentMethods);
             } catch (\Exception $exception) {
                 $this->logger->error('Could not fetch adyen payment methods: ' . $exception->getMessage());
             }
         }

         return $this->data ?? '';
     }
}
