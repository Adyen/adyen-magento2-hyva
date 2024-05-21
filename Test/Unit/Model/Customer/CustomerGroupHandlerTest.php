<?php

namespace Adyen\Hyva\Test\Unit\Model\Customer;

use Adyen\Hyva\Model\Customer\CustomerGroupHandler;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;

class CustomerGroupHandlerTest extends \PHPUnit\Framework\TestCase
{
    private MockObject $session;
    private MockObject $logger;

    private CustomerGroupHandler  $customerGroupHandler;
    public function setUp(): void
    {
        $this->session = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerGroupHandler = new CustomerGroupHandler(
            $this->session,
            $this->logger
        );
    }

    public function testUserIsGuest()
    {
        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCustomerId'])
            ->getMock();

        $this->session->expects($this->once())
            ->method('getQuote')
            ->willReturn($quote);

        $quote->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(null);

        $this->assertTrue($this->customerGroupHandler->userIsGuest());
    }

    public function testUserIsCustomer()
    {
        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCustomerId'])
            ->getMock();

        $this->session->expects($this->once())
            ->method('getQuote')
            ->willReturn($quote);

        $quote->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(45);

        $this->assertFalse($this->customerGroupHandler->userIsGuest());
    }
}
