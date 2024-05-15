<?php

namespace Adyen\Hyva\Test\Unit\Model\CreditCard;

use Adyen\Hyva\Model\CreditCard\BrandsManager;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;

class BrandsManagerTest extends \PHPUnit\Framework\TestCase
{
    private MockObject $quote;
    private MockObject $session;
    private MockObject $paymentMethods;
    private MockObject $serializer;
    private MockObject $logger;
    private BrandsManager $brandsManager;

    public function setUp(): void
    {
        $this->quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
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

    /**
     * @dataProvider inputProviderBrandsManager
     */
    public function testGetBrandsAsArrayConsecutive($quoteId, $brands, $brandsSerialized, $paymentMethodsResponse)
    {
        $this->setExpectations($quoteId, $brands, $brandsSerialized, $paymentMethodsResponse);

        $this->brandsManager->getBrandsAsArray();
        $result = $this->brandsManager->getBrandsAsArray();

        $this->assertEquals($brands, $result);
    }

    /**
     * @dataProvider inputProviderBrandsManager
     */
    public function testGetBrands($quoteId, $brands, $brandsSerialized, $paymentMethodsResponse)
    {
        $this->setExpectations($quoteId, $brands, $brandsSerialized, $paymentMethodsResponse, true);

        $this->assertEquals($brandsSerialized, $this->brandsManager->getBrands());
    }

    public function inputProviderBrandsManager(): array
    {
        return [
            '#1' => [
                'quoteId' => 456,
                'brands' => ['mc', 'visa'],
                'brandsSerialized' => json_encode(['mc', 'visa']),
                'paymentMethodsResponse' => [
                    'paymentMethodsResponse' => [
                        'paymentMethods' => [
                            'card' => [
                                'type' => 'scheme',
                                'brands' => ['mc', 'visa'],
                            ],
                            'somethings_irrelevant' => [
                                'type' => 'somethings_irrelevant',
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
        $this->session->expects($this->exactly(2))
            ->method('getQuote')
            ->willReturn($this->quote);

        $this->quote->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($quoteId);

        $this->paymentMethods->expects($this->once())
            ->method('getDataAsArray')
            ->with($this->equalTo($quoteId))
            ->willReturn($paymentMethodsResponse);

        $this->serializer->expects($serialize ? $this->once() : $this->never())
            ->method('serialize')
            ->with($brands)
            ->willReturn($brandsSerialized);
    }
}
