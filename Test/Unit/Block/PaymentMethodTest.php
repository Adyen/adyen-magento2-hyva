<?php

namespace Adyen\Hyva\Test\Unit\Block;

use Adyen\Hyva\Block\PaymentMethod;
use Adyen\Hyva\Model\Configuration;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;

class PaymentMethodTest extends \PHPUnit\Framework\TestCase
{
    private MockObject $quote;
    private MockObject $address;
    private MockObject $context;
    private MockObject $configuration;
    private MockObject $methodList;
    private MockObject $session;
    private MockObject $jsonSerializer;

    private PaymentMethod $paymentMethod;

    public function setUp(): void
    {
        $this->quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->address = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $this->configuration = $this->createMock(\Adyen\Hyva\Model\Configuration::class);
        $this->methodList = $this->createMock(\Adyen\Hyva\Model\MethodList::class);
        $this->session = $this->createMock(\Magento\Checkout\Model\Session::class);
        $this->jsonSerializer = $this->createMock(\Magento\Framework\Serialize\Serializer\Json::class);

        $this->paymentMethod = new PaymentMethod(
            $this->context,
            $this->configuration,
            $this->methodList,
            $this->session,
            $this->jsonSerializer
        );
    }

    public function testGetConfiguration()
    {
        $this->assertInstanceOf(Configuration::class, $this->paymentMethod->getConfiguration());
    }

    public function testGetAvailableMethods()
    {
        $availableMethods = [
            'adyen_cc',
            'adyen_googlepay'
        ];

        $this->methodList->expects($this->once())
            ->method('collectAvailableMethods')
            ->willReturn($availableMethods);

        $this->assertEquals($availableMethods,  $this->paymentMethod->getAvailableMethods());
    }

    public function testGetQuoteShippingAddress()
    {
        $data = [
            'some-key' => 'some-value'
        ];

        $this->setQuoteShippingAddressCommmonExpectations();

        $this->address->expects($this->once())->method('getData')->willReturn($data);
        $this->jsonSerializer->expects($this->once())->method('serialize')->willReturn(json_encode($data));

        $this->assertEquals(json_encode($data), $this->paymentMethod->getQuoteShippingAddress());
    }

    public function testGetQuoteShippingAddressWithException(): void
    {
        $this->setQuoteShippingAddressCommmonExpectations();

        $this->address->expects($this->once())->method('getData')
            ->willThrowException(new \InvalidArgumentException());
        $this->jsonSerializer->expects($this->once())
            ->method('serialize')
            ->willReturn('[]');

        $this->assertEquals(json_encode([]), $this->paymentMethod->getQuoteShippingAddress());
    }

    private function setQuoteShippingAddressCommmonExpectations()
    {
        $this->session->expects($this->once())->method('getQuote')->willReturn($this->quote);
        $this->quote->expects($this->exactly(2))->method('getShippingAddress')->willReturn($this->address);
    }


    public function testGetQuoteBillingAddress()
    {
        $data = [
            'some-key' => 'some-value'
        ];

        $this->session->expects($this->once())->method('getQuote')->willReturn($this->quote);
        $this->quote->expects($this->exactly(2))->method('getBillingAddress')->willReturn($this->address);
        $this->address->expects($this->once())->method('getData')->willReturn($data);
        $this->jsonSerializer->expects($this->once())->method('serialize')->willReturn(json_encode($data));

        $this->assertEquals(json_encode($data), $this->paymentMethod->getQuoteBillingAddress());
    }
}
