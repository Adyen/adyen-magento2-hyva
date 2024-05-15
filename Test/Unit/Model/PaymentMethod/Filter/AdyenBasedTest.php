<?php

namespace Adyen\Hyva\Test\Unit\Model\PaymentMethod\Filter;

use Adyen\Hyva\Model\PaymentMethod\Filter\AdyenBased;
use PHPUnit\Framework\MockObject\MockObject;

class AdyenBasedTest extends \PHPUnit\Framework\TestCase
{
    private MockObject $adyenCc;
    private MockObject $adyenGPay;
    private MockObject $adyenNotSupported;
    private MockObject $methodList;

    private AdyenBased $adyenBased;

    public function setUp(): void
    {
        $this->adyenCc = $this->getMockBuilder(\Magento\Quote\Api\Data\PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode', 'getTitle'])
            ->getMock();
        $this->adyenGPay = $this->getMockBuilder(\Magento\Quote\Api\Data\PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode', 'getTitle'])
            ->getMock();
        $this->adyenNotSupported = $this->getMockBuilder(\Magento\Quote\Api\Data\PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode', 'getTitle'])
            ->getMock();

        $this->methodList = $this->createMock(\Adyen\Hyva\Model\MethodList::class);

        $this->adyenBased = new AdyenBased($this->methodList);
    }

    public function testExecute()
    {
        $supportedMethods =             [
            'adyen_cc',
            'adyen_googlepay'
        ];

        $list = [
            'adyen_cc' => $this->adyenCc,
            'adyen_googlepay' => $this->adyenGPay,
            'adyen_unsuported' => $this->adyenNotSupported
        ];

        $this->adyenCc->expects($this->exactly(2))->method('getCode')->willReturn('adyen_cc');
        $this->adyenGPay->expects($this->exactly(2))->method('getCode')->willReturn('adyen_googlepay');
        $this->adyenNotSupported->expects($this->exactly(2))->method('getCode')->willReturn('adyen_unsuported');

        $this->methodList->expects($this->exactly(3))
            ->method('collectAvailableMethods')
            ->willReturn($supportedMethods);

        $result = $this->adyenBased->execute(123, $list);

        $this->assertEquals(
            $supportedMethods,
            array_keys($result)
        );

        $this->assertArrayNotHasKey('adyen_unsuported', array_keys($result));
    }
}
