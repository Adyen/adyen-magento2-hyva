<?php

namespace Adyen\Hyva\Test\Unit\Model\PaymentMethod\Filter;

use Adyen\Hyva\Model\PaymentMethod\Filter\AdyenBased;
use PHPUnit\Framework\MockObject\MockObject;

class AdyenBasedTest extends \PHPUnit\Framework\TestCase
{
    private MockObject $methodList;

    private AdyenBased $adyenBased;

    public function setUp(): void
    {
        $this->methodList = $this->createMock(\Adyen\Hyva\Model\MethodList::class);

        $this->adyenBased = new AdyenBased($this->methodList);
    }

    public function testExecute()
    {
        $supportedMethods =             [
            'adyen_cc',
            'adyen_googlepay'
        ];

        $adyenCc = $this->getMockBuilder(\Magento\Quote\Api\Data\PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode', 'getTitle'])
            ->getMock();
        $adyenGPay = $this->getMockBuilder(\Magento\Quote\Api\Data\PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode', 'getTitle'])
            ->getMock();
        $adyenNotSupported = $this->getMockBuilder(\Magento\Quote\Api\Data\PaymentMethodInterface::class)
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
