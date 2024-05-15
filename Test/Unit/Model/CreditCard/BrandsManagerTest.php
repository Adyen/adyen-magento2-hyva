<?php

namespace Adyen\Hyva\Test\Unit\Model\CreditCard;

use Adyen\Hyva\Model\CreditCard\BrandsManager;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;

class BrandsManagerTest extends \PHPUnit\Framework\TestCase
{
    private MockObject $session;
    private MockObject $paymentMethods;
    private MockObject $serializer;
    private MockObject $logger;
    private BrandsManager $brandsManager;

    public function setUp(): void
    {
        $this->session = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentMethods = $this->getMockBuilder(\Adyen\Hyva\Model\PaymentMethod\PaymentMethods::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->brandsManager = new BrandsManager(
            $this->session,
            $this->paymentMethods,
            $this->serializer,
            $this->logger
        );
    }

    public function testGetBrandsAsArray()
    {
        $quoteId = 123;
        $brands = ['mc', 'visa'];
        $paymentMethods = [
            'card' => [
                'type' => 'scheme',
                'brands' => $brands,
            ],
            'somethings_else' => [
                'type' => 'something_else',
                'brands' => [],
            ],
        ];
        $paymentMethodsResponse = ['paymentMethodsResponse' => ['paymentMethods' => $paymentMethods]];
        $quote = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()->getMock();

        $this->session->expects($this->exactly(2))
            ->method('getQuote')
            ->willReturn($quote);

        $quote->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($quoteId);

        $this->paymentMethods->expects($this->once())
            ->method('getDataAsArray')
            ->with($this->equalTo($quoteId))
            ->willReturn($paymentMethodsResponse);

        $this->assertEquals($brands, $this->brandsManager->getBrandsAsArray());
    }

    public function testGetBrandsAsArrayConsecutive()
    {
        $quoteId = 123;
        $brands = ['mc', 'visa'];
        $paymentMethods = [
            'card' => [
                'type' => 'scheme',
                'brands' => $brands,
            ],
            'somethings_else' => [
                'type' => 'something_else',
                'brands' => [],
            ],
        ];
        $paymentMethodsResponse = ['paymentMethodsResponse' => ['paymentMethods' => $paymentMethods]];
        $quote = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()->getMock();

        $this->session->expects($this->exactly(2))
            ->method('getQuote')
            ->willReturn($quote);

        $quote->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($quoteId);

        $this->paymentMethods->expects($this->once())
            ->method('getDataAsArray')
            ->with($this->equalTo($quoteId))
            ->willReturn($paymentMethodsResponse);

        $this->brandsManager->getBrandsAsArray();
        $result = $this->brandsManager->getBrandsAsArray();
        $this->assertEquals($brands, $result);
    }

    public function testGetBrands()
    {
        $quoteId = 123;
        $brands = ['mc', 'visa'];
        $brandsSerialized = json_encode($brands);
        $paymentMethods = [
            'card' => [
                'type' => 'scheme',
                'brands' => $brands,
            ],
            'somethings_else' => [
                'type' => 'something_else',
                'brands' => [],
            ],
        ];
        $paymentMethodsResponse = ['paymentMethodsResponse' => ['paymentMethods' => $paymentMethods]];
        $quote = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()->getMock();

        $this->session->expects($this->exactly(2))
            ->method('getQuote')
            ->willReturn($quote);

        $quote->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($quoteId);

        $this->paymentMethods->expects($this->once())
            ->method('getDataAsArray')
            ->with($this->equalTo($quoteId))
            ->willReturn($paymentMethodsResponse);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($brands)
            ->willReturn($brandsSerialized);

        $this->assertEquals($brandsSerialized, $this->brandsManager->getBrands());
    }
}
