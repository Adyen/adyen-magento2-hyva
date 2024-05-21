<?php

namespace Adyen\Hyva\Test\Unit\Magewire\Checkout;

use Adyen\Hyva\Magewire\Checkout\Success;
use Adyen\Hyva\Model\Customer\CustomerGroupHandler;
use Adyen\Payment\Api\AdyenDonationsInterface;
use Adyen\Payment\Api\GuestAdyenDonationsInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SuccessTest extends TestCase
{
    private MockObject $adyenDonations;
    private MockObject $adyenGuestDonations;
    private MockObject $customerGroupHandler;
    private MockObject $logger;

    private Success $success;

    protected function setUp(): void
    {
        $this->adyenDonations = $this->createMock(AdyenDonationsInterface::class);
        $this->adyenGuestDonations = $this->createMock(GuestAdyenDonationsInterface::class);
        $this->customerGroupHandler = $this->createMock(CustomerGroupHandler::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->success = new Success(
            $this->adyenDonations,
            $this->adyenGuestDonations,
            $this->customerGroupHandler,
            $this->logger
        );
    }

    public function testUserIsNotGuest()
    {
        $this->customerGroupHandler->expects($this->once())->method('userIsGuest')->willReturn(false);

        $this->assertFalse($this->success->userIsGuest());
    }

    public function testUserIsGuest()
    {
        $this->customerGroupHandler->expects($this->once())->method('userIsGuest')->willReturn(true);

        $this->assertTrue($this->success->userIsGuest());
    }

    public function testDonate()
    {
        $orderId = 123;
        $payload = ['some-key' => 'some-value'];

        $this->adyenDonations->expects($this->once())
            ->method('donate');

        $this->success->donate($orderId, $payload);

        $this->assertTrue($this->success->donationStatus);
    }

    public function testDonateFail()
    {
        $orderId = 123;
        $payload = ['some-key' => 'some-value'];
        $errorMessage = 'some-error';

        $this->adyenDonations->expects($this->once())
            ->method('donate')
            ->willThrowException(new \Exception($errorMessage));

        $this->logger->expects($this->once())
            ->method('error')
            ->with($errorMessage);

        $this->success->donate($orderId, $payload);

        $this->assertFalse($this->success->donationStatus);
    }

    public function testDonateGuest()
    {
        $maskedCartId = '12344fff';
        $payload = ['some-key' => 'some-value'];

        $this->adyenGuestDonations->expects($this->once())
            ->method('donate');

        $this->success->donateGuest($maskedCartId, $payload);

        $this->assertTrue($this->success->donationStatus);
    }

    public function testDonateGuestFail()
    {
        $maskedCartId = '12344fff';
        $payload = ['some-key' => 'some-value'];
        $errorMessage = 'some-error';

        $this->adyenGuestDonations->expects($this->once())
            ->method('donate')
            ->willThrowException(new \Exception($errorMessage));

        $this->logger->expects($this->once())
            ->method('error')
            ->with($errorMessage);

        $this->success->donateGuest($maskedCartId, $payload);

        $this->assertFalse($this->success->donationStatus);
    }
}
