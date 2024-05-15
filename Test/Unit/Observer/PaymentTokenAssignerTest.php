<?php

namespace Adyen\Hyva\Test\Unit\Observer;

use Adyen\Hyva\Observer\PaymentTokenAssigner;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Payment;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PaymentTokenAssignerTest extends TestCase
{
    private MockObject $session;
    private MockObject $paymentTokenManagement;
    private MockObject $logger;
    private MockObject $observer;
    private MockObject $event;

    private PaymentTokenAssigner $paymentTokenAssigner;

    protected function setUp(): void
    {
        $this->session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->addMethods(['getStoredCardPublicHash'])
            ->getMock();
        $this->paymentTokenManagement = $this->createMock(\Magento\Vault\Api\PaymentTokenManagementInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->observer = $this->createMock(Observer::class);
        $this->event = $this->getMockBuilder(Event::class)
            ->onlyMethods(['getDataByKey'])
            ->getMock();

        $this->paymentTokenAssigner = new PaymentTokenAssigner(
            $this->session,
            $this->paymentTokenManagement,
            $this->logger
        );
    }

    public function testUpdateAdditionalInformation()
    {
        $additionalInformation = [];
        $tokenPublicHash = '2525kkkk';
        $paymentToken = '6777kkk';
        $customerId = 123;
        $additionalInformationUpdated = $additionalInformation;
        $additionalInformationUpdated[PaymentTokenInterface::PUBLIC_HASH] = $tokenPublicHash;
        $additionalInformationUpdated[PaymentTokenInterface::CUSTOMER_ID] = $customerId;

        $payment = $this->getMockBuilder(Payment::class)->disableOriginalConstructor()->getMock();
        $quote = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()->getMock();
        $customer = $this->getMockBuilder(Customer::class)->disableOriginalConstructor()->getMock();

        $this->observer->method('getEvent')->willReturn($this->event);
        $this->event->method('getDataByKey')
            ->with('payment_model')
            ->willReturn($payment);

        $this->session->expects($this->once())
            ->method('getStoredCardPublicHash')
            ->willReturn($tokenPublicHash);

        $payment->expects($this->once())
            ->method('getQuote')
            ->willReturn($quote);

        $quote->expects($this->once())
            ->method('getCustomer')
            ->willReturn($customer);

        $customer->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);

        $this->paymentTokenManagement->expects($this->once())
            ->method('getByPublicHash')
            ->with($tokenPublicHash, $customerId)
            ->willReturn($paymentToken);

        $payment->expects($this->once())
            ->method('getAdditionalInformation')
            ->willReturn($additionalInformation);

        $payment->expects($this->once())
            ->method('setAdditionalInformation')
            ->with($additionalInformationUpdated);

        $this->paymentTokenAssigner->execute($this->observer);
    }
}
