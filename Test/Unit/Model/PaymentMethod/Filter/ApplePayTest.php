<?php

namespace Adyen\Hyva\Test\Unit\Model\PaymentMethod\Filter;

use Adyen\Hyva\Model\PaymentMethod\Filter\ApplePay;
use Magento\Framework\App\Request\Http;
use Magento\Quote\Api\Data\PaymentMethodInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ApplePayTest extends TestCase
{
    private MockObject $http;

    private ApplePay $applePay;

    public function setUp(): void
    {
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

        $adyenCc = $this->getMockBuilder(PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode', 'getTitle'])
            ->getMock();
        $adyenGPay = $this->getMockBuilder(PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode', 'getTitle'])
            ->getMock();
        $adyenApplePay = $this->getMockBuilder(PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode', 'getTitle'])
            ->getMock();


        $list = [
            'adyen_cc' => $adyenCc,
            'adyen_googlepay' => $adyenGPay,
            'adyen_applepay' => $adyenApplePay
        ];

        $adyenApplePay->expects($this->never())->method('getCode')->willReturn('adyen_applepay');

        $this->http->expects($this->once())
            ->method('getServerValue')
            ->willReturn('something-with-safari-in-it');

        $result = $this->applePay->execute(123, $list);

        $this->assertEquals(
            $supportedMethods,
            array_keys($result)
        );
    }

    public function testExecuteWithChrome()
    {
        $supportedMethods = [
            'adyen_cc',
            'adyen_googlepay'
        ];

        $adyenCc = $this->getMockBuilder(PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode', 'getTitle'])
            ->getMock();
        $adyenGPay = $this->getMockBuilder(PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode', 'getTitle'])
            ->getMock();
        $adyenApplePay = $this->getMockBuilder(PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode', 'getTitle'])
            ->getMock();


        $list = [
            'adyen_cc' => $adyenCc,
            'adyen_googlepay' => $adyenGPay,
            'adyen_applepay' => $adyenApplePay
        ];

        $adyenApplePay->expects($this->once())->method('getCode')->willReturn('adyen_applepay');

        $this->http->expects($this->once())
            ->method('getServerValue')
            ->willReturn('something-with-chrome-and-safari-in-it');

        $result = $this->applePay->execute(123, $list);

        $this->assertEquals(
            $supportedMethods,
            array_keys($result)
        );
    }

    public function testExecuteWithAnythingElse()
    {
        $supportedMethods = [
            'adyen_cc',
            'adyen_googlepay'
        ];

        $adyenCc = $this->getMockBuilder(PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode', 'getTitle'])
            ->getMock();
        $adyenGPay = $this->getMockBuilder(PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode', 'getTitle'])
            ->getMock();
        $adyenApplePay = $this->getMockBuilder(PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode', 'getTitle'])
            ->getMock();


        $list = [
            'adyen_cc' => $adyenCc,
            'adyen_googlepay' => $adyenGPay,
            'adyen_applepay' => $adyenApplePay
        ];

        $adyenApplePay->expects($this->once())->method('getCode')->willReturn('adyen_applepay');

        $this->http->expects($this->once())
            ->method('getServerValue')
            ->willReturn('something-with-anything-else-in-it');

        $result = $this->applePay->execute(123, $list);

        $this->assertEquals(
            $supportedMethods,
            array_keys($result)
        );
    }
}
