<?php

namespace Adyen\Hyva\Test\Unit\Observer;

use Adyen\Hyva\Observer\InstallmentsDataAssigner;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Quote\Model\Quote\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class InstallmentsDataAssignerTest extends TestCase
{
    private MockObject $session;
    private MockObject $logger;
    private MockObject $observer;
    private MockObject $event;

    private InstallmentsDataAssigner $installmentsDataAssigner;

    protected function setUp(): void
    {
        $this->session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->addMethods(['getNumberOfInstallments', 'getCcType'])
            ->getMock();
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->observer = $this->createMock(Observer::class);
        $this->event = $this->getMockBuilder(Event::class)
            ->onlyMethods(['getDataByKey'])
            ->getMock();

        $this->installmentsDataAssigner = new InstallmentsDataAssigner($this->session, $this->logger);
    }

    public function testUpdateAdditionalInformation()
    {
        $additionalInformation = [];
        $numberOfInstallments = 4;
        $ccType = 'mc';
        $additionalInformationUpdated = $additionalInformation;
        $additionalInformationUpdated['number_of_installments'] = $numberOfInstallments;
        $additionalInformationUpdated['cc_type'] = $ccType;

        $payment = $this->getMockBuilder(Payment::class)->disableOriginalConstructor()->getMock();

        $this->observer->method('getEvent')->willReturn($this->event);
        $this->event->method('getDataByKey')
            ->with('payment_model')
            ->willReturn($payment);

        $this->session->expects($this->exactly(2))
            ->method('getNumberOfInstallments')
            ->willReturn($numberOfInstallments);

        $this->session->expects($this->exactly(2))
            ->method('getCcType')
            ->willReturn($ccType);

        $payment->expects($this->once())
            ->method('getAdditionalInformation')
            ->willReturn($additionalInformation);

        $payment->expects($this->once())
            ->method('setAdditionalInformation')
            ->with($additionalInformationUpdated);

        $this->installmentsDataAssigner->execute($this->observer);
    }
}
