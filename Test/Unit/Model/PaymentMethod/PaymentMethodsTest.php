<?php

namespace Adyen\Hyva\Test\Unit\Model\PaymentMethod;

use Adyen\Hyva\Model\PaymentMethod\PaymentMethods;
use PHPUnit\Framework\MockObject\MockObject;

class PaymentMethodsTest extends \PHPUnit\Framework\TestCase
{
    private MockObject $adyenPaymentMethods;
    private MockObject $serializer;
    private MockObject $logger;

    private PaymentMethods $paymentMethods;

    public function setUp(): void
    {
        $this->adyenPaymentMethods = $this->getMockBuilder(\Adyen\Payment\Helper\PaymentMethods::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentMethods = new PaymentMethods(
            $this->adyenPaymentMethods,
            $this->serializer,
            $this->logger
        );
    }

    /**
     * @dataProvider inputProviderPaymentMethods
     */
    public function testGetDataAsArrayConsecutive($quoteId, $brands, $brandsSerialized, $paymentMethodsResponse)
    {
        $this->setExpectations($quoteId, $brands, $brandsSerialized, $paymentMethodsResponse);

        $this->paymentMethods->getDataAsArray($quoteId);
        $result = $this->paymentMethods->getDataAsArray($quoteId);

        $this->assertEquals($paymentMethodsResponse, $result);
    }

    public function testGetDataAsArrayWhenExceptionIsThrownFromAdyenPaymentMethods()
    {
        $quoteId = 123;
        $exceptionMessage = 'An error occurred from Adyen endpoint';
        $exception = new \Exception($exceptionMessage);

        $this->adyenPaymentMethods->expects($this->once())
            ->method('getPaymentMethods')
            ->willThrowException($exception);

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Could not fetch adyen payment methods: ' . $exceptionMessage);

        $this->assertEquals([], $this->paymentMethods->getDataAsArray($quoteId));
    }

    /**
     * @dataProvider inputProviderPaymentMethods
     */
    public function testGetDataConsecutive($quoteId, $brands, $brandsSerialized, $paymentMethodsResponse)
    {
        $this->setExpectations($quoteId, $brands, $brandsSerialized, $paymentMethodsResponse, true);

        $this->paymentMethods->getData($quoteId);
        $result = $this->paymentMethods->getData($quoteId);

        $this->assertEquals(json_encode($paymentMethodsResponse), $result);
    }

    public function inputProviderPaymentMethods(): array
    {
        return [
            '#1' => [
                'quoteId' => 123,
                'brands' => ['mc', 'visa'],
                'brandsSerialized' => json_encode(['mc', 'visa']),
                'paymentMethodsResponse' => [
                    'paymentMethodsResponse' => [
                        'paymentMethods' => [
                            'card' => [
                                'type' => 'scheme',
                                'brands' => ['mc', 'visa'],
                            ],
                            'somethings_else' => [
                                'type' => 'something_else',
                                'brands' => [],
                            ],
                        ]
                    ]
                ]
            ]
        ];
    }

    private function setExpectations($quoteId, $brands, $brandsSerialized, $paymentMethodsResponse, $serialize = false)
    {
        $this->adyenPaymentMethods->expects($this->once())
            ->method('getPaymentMethods')
            ->willReturn(json_encode($paymentMethodsResponse));

        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->with(json_encode($paymentMethodsResponse))
            ->willReturn($paymentMethodsResponse);

        $this->serializer->expects($serialize ? $this->once() : $this->never())
            ->method('serialize')
            ->with($paymentMethodsResponse)
            ->willReturn(json_encode($paymentMethodsResponse));
    }
}
