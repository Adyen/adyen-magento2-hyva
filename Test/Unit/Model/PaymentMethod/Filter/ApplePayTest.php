<?php

namespace Adyen\Hyva\Test\Unit\Model\PaymentMethod\Filter;

use Adyen\Hyva\Model\PaymentMethod\Filter\ApplePay;
use Magento\Framework\App\Request\Http;
use Magento\Quote\Api\Data\PaymentMethodInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ApplePayTest extends TestCase
{
    private MockObject $adyenCc;
    private MockObject $adyenGPay;
    private MockObject $adyenApplePay;
    private MockObject $http;

    private ApplePay $applePay;

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
        $this->adyenApplePay = $this->getMockBuilder(PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode', 'getTitle'])
            ->getMock();

        $this->http = $this->createMock(Http::class);

        $this->applePay = new ApplePay($this->http);
    }

    public function testExecuteWithSafari()
    {
        $supportedMethods = [
            'adyen_cc',
            'adyen_googlepay',
            'adyen_applepay'
        ];

        $list = $this->getList();

        $this->http->expects($this->once())
            ->method('getServerValue')
            ->willReturn('something-with-safari-in-it');

        $this->adyenApplePay->expects($this->never())->method('getCode')->willReturn('adyen_applepay');

        $result = $this->applePay->execute(123, $list);

        $this->doAssert($supportedMethods, $result);
    }

    public function testExecuteWithChrome()
    {
        $supportedMethods = [
            'adyen_cc',
            'adyen_googlepay'
        ];

        $this->http->expects($this->once())
            ->method('getServerValue')
            ->willReturn('something-with-chrome-and-safari-in-it');

        $this->adyenApplePay->expects($this->once())->method('getCode')->willReturn('adyen_applepay');

        $result = $this->applePay->execute(123, $this->getList());

        $this->doAssert($supportedMethods, $result);
    }

    public function testExecuteWithAnythingElse()
    {
        $supportedMethods = [
            'adyen_cc',
            'adyen_googlepay'
        ];

        $this->http->expects($this->once())
            ->method('getServerValue')
            ->willReturn('something-with-anything-else-in-it');

        $this->adyenApplePay->expects($this->once())->method('getCode')->willReturn('adyen_applepay');

        $result = $this->applePay->execute(123, $this->getList());

        $this->doAssert($supportedMethods, $result);
    }

    private function getList(): array
    {
        return [
            'adyen_cc' => $this->adyenCc,
            'adyen_googlepay' => $this->adyenGPay,
            'adyen_applepay' => $this->adyenApplePay
        ];
    }

    private function doAssert(array $supportedMethods, array $result)
    {
        $this->assertEquals(
            $supportedMethods,
            array_keys($result)
        );
    }
}
