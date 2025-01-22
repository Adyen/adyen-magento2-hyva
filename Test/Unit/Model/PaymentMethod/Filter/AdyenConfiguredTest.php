<?php

namespace Adyen\Hyva\Test\Unit\Model\PaymentMethod\Filter;

use Adyen\Hyva\Model\PaymentMethod\Filter\AdyenConfigured;
use Adyen\Hyva\Model\PaymentMethod\PaymentMethods;
use Magento\Quote\Api\Data\PaymentMethodInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AdyenConfiguredTest extends TestCase
{
    private MockObject $adyenCc;
    private MockObject $adyenGPay;
    private MockObject $adyenNotConfigured;
    private MockObject $paymentMethods;

    private AdyenConfigured $adyenConfigured;

    public function setUp(): void
    {
        $this->adyenCc = $this->getMockBuilder(PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode', 'getTitle'])
            ->getMock();
        $this->adyenGPay = $this->getMockBuilder(PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode', 'getTitle'])
            ->getMock();
        $this->adyenNotConfigured = $this->getMockBuilder(PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode', 'getTitle'])
            ->getMock();

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
            'scheme' => [],
            'googlepay' => [],
            'bmcm' => []
        ];

        $paymentMethodsResponse = ['paymentMethodsExtraDetails' => $paymentMethods];
        $paymentMethodsResponseSerialized = json_encode($paymentMethodsResponse);

        $list = [
            'adyen_cc' => $this->adyenCc,
            'adyen_googlepay' => $this->adyenGPay,
            'adyen_not_configured' => $this->adyenNotConfigured
        ];

        $this->adyenCc->expects($this->exactly(2))->method('getCode')->willReturn('adyen_cc');
        $this->adyenGPay->expects($this->exactly(2))->method('getCode')->willReturn('adyen_googlepay');
        $this->adyenNotConfigured->expects($this->exactly(2))->method('getCode')->willReturn('adyen_not_configured');

        $this->paymentMethods->expects($this->once())
            ->method('getData')
            ->willReturn($paymentMethodsResponseSerialized);

        $result = $this->adyenConfigured->execute(123, $list);

        $this->assertEquals(
            $supportedMethods,
            array_keys($result)
        );

        $this->assertArrayNotHasKey('adyen_not_configured', array_keys($result));
    }
}
