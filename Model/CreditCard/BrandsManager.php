<?php

declare(strict_types=1);

namespace Adyen\Hyva\Model\CreditCard;

use Adyen\Payment\Api\AdyenPaymentMethodManagementInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

class BrandsManager
{
    private ?array $brandsData = null;

    public function __construct(
        private readonly Session $session,
        private readonly AdyenPaymentMethodManagementInterface $adyenPaymentMethodManagement,
        private readonly Json $jsonSerializer,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @return array
     */
    public function getBrandsAsArray(): array
    {
        if ($this->brandsData === null) {
            try {
                if ($this->session->getQuote()->getId()) {
                    $paymentMethodsResponse = json_decode(
                        $this->adyenPaymentMethodManagement->getPaymentMethods(
                            strval($this->session->getQuote()->getId())
                        ),
                        true
                    );

                    if (isset($paymentMethodsResponse['paymentMethodsResponse']['paymentMethods'])) {
                        foreach ($paymentMethodsResponse['paymentMethodsResponse']['paymentMethods'] as $paymentMethod) {
                            if ($paymentMethod['type'] == 'scheme') {
                                $this->brandsData = $paymentMethod['brands'];
                            }
                        }
                    }
                }
            } catch (\Exception $exception) {
                $this->logger->error('Could not fetch brands data: ' . $exception->getMessage());
            }
        }

        return $this->brandsData ?? [];
    }

    /**
     * @return string
     */
    public function getBrands(): string
    {
        $brands = $this->getBrandsAsArray();

        return $this->jsonSerializer->serialize($brands);
    }
}
