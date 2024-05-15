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

    public function testGetDataAsArray()
    {
        $quoteId = 123;
        $paymentMethods = [
            'card' => [
                'type' => 'scheme',
                'brands' => ['mc'],
            ],
            'somethings_else' => [
                'type' => 'something_else',
                'brands' => [],
            ],
        ];
        $paymentMethodsResponse = ['paymentMethodsResponse' => ['paymentMethods' => $paymentMethods]];
        $paymentMethodsResponseSerialized = json_encode($paymentMethodsResponse);

        $this->adyenPaymentMethods->expects($this->once())
            ->method('getPaymentMethods')
            ->willReturn($paymentMethodsResponseSerialized);

        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->with($paymentMethodsResponseSerialized)
            ->willReturn($paymentMethodsResponse);

        $this->assertEquals($paymentMethodsResponse, $this->paymentMethods->getDataAsArray($quoteId));
    }

    public function testGetDataAsArrayConsecutive()
    {
        $quoteId = 123;
        $paymentMethods = [
            'card' => [
                'type' => 'scheme',
                'brands' => ['mc'],
            ],
            'somethings_else' => [
                'type' => 'something_else',
                'brands' => [],
            ],
        ];
        $paymentMethodsResponse = ['paymentMethodsResponse' => ['paymentMethods' => $paymentMethods]];
        $paymentMethodsResponseSerialized = json_encode($paymentMethodsResponse);

        $this->adyenPaymentMethods->expects($this->once())
            ->method('getPaymentMethods')
            ->willReturn($paymentMethodsResponseSerialized);

        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->with($paymentMethodsResponseSerialized)
            ->willReturn($paymentMethodsResponse);

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

    public function testGetDataConsecutive()
    {
        $quoteId = 123;
        $paymentMethods = [
            'card' => [
                'type' => 'scheme',
                'brands' => ['mc'],
            ],
            'somethings_else' => [
                'type' => 'something_else',
                'brands' => [],
            ],
        ];
        $paymentMethodsResponse = ['paymentMethodsResponse' => ['paymentMethods' => $paymentMethods]];
        $paymentMethodsResponseSerialized = json_encode($paymentMethodsResponse);

        $this->adyenPaymentMethods->expects($this->once())
            ->method('getPaymentMethods')
            ->willReturn($paymentMethodsResponseSerialized);

        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->with($paymentMethodsResponseSerialized)
            ->willReturn($paymentMethodsResponse);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($paymentMethodsResponse)
            ->willReturn($paymentMethodsResponseSerialized);

        $this->paymentMethods->getData($quoteId);
        $result = $this->paymentMethods->getData($quoteId);

        $this->assertEquals($paymentMethodsResponseSerialized, $result);
    }
}
