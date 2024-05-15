<?php

namespace Adyen\Hyva\Test\Unit\Model\PaymentMethod\Filter;

use Adyen\Hyva\Model\PaymentMethod\Filter\AdyenConfigured;
use Adyen\Hyva\Model\PaymentMethod\PaymentMethods;
use Magento\Quote\Api\Data\PaymentMethodInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AdyenConfiguredTest extends TestCase
{
    private MockObject $paymentMethods;

    private AdyenConfigured $adyenConfigured;

    public function setUp(): void
    {
        $this->paymentMethods = $this->createMock(PaymentMethods::class);

        $this->adyenConfigured = new AdyenConfigured($this->paymentMethods);
    }

    public function testExecute()
    {
        $supportedMethods =             [
            'adyen_cc',
            'adyen_googlepay'
        ];

        $paymentMethods = [
            'card' => [],
            'googlepay' => [],
            'bmcm' => []
        ];

        $paymentMethodsResponse = ['paymentMethodsExtraDetails' => $paymentMethods];
        $paymentMethodsResponseSerialized = json_encode($paymentMethodsResponse);

        $adyenCc = $this->getMockBuilder(PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode', 'getTitle'])
            ->getMock();
        $adyenGPay = $this->getMockBuilder(PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode', 'getTitle'])
            ->getMock();
        $adyenNotSupported = $this->getMockBuilder(PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode', 'getTitle'])
            ->getMock();

        $list = [
            'adyen_cc' => $adyenCc,
            'adyen_googlepay' => $adyenGPay,
            'adyen_unsuported' => $adyenNotSupported
        ];

        $adyenCc->expects($this->exactly(2))->method('getCode')->willReturn('adyen_cc');
        $adyenGPay->expects($this->exactly(2))->method('getCode')->willReturn('adyen_googlepay');
        $adyenNotSupported->expects($this->exactly(2))->method('getCode')->willReturn('adyen_unsuported');

        $this->paymentMethods->expects($this->once())
            ->method('getData')
            ->willReturn($paymentMethodsResponseSerialized);

        $result = $this->adyenConfigured->execute(123, $list);

        $this->assertEquals(
            $supportedMethods,
            array_keys($result)
        );

        $this->assertArrayNotHasKey('adyen_unsuported', array_keys($result));
    }
}
