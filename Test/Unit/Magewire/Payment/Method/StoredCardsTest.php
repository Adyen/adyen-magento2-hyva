<?php

namespace Adyen\Hyva\Test\Unit\Magewire\Payment\Method;

use Adyen\Hyva\Api\ProcessingMetadataInterface;
use Adyen\Hyva\Magewire\Payment\Method\CreditCard;
use Adyen\Hyva\Magewire\Payment\Method\StoredCards;
use Adyen\Hyva\Model\Component\Payment\Context;
use Adyen\Hyva\Model\CreditCard\InstallmentsManager;
use Adyen\Payment\Api\AdyenOrderPaymentStatusInterface;
use Adyen\Payment\Test\Unit\AbstractAdyenTestCase;
use Hyva\Checkout\Model\Magewire\Component\Evaluation\Success;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Checkout\Model\Session;
use Magento\Quote\Api\Data\PaymentExtensionFactory;
use Magento\Quote\Api\Data\PaymentExtensionInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Payment;
use PHPUnit\Framework\MockObject\MockObject;

class StoredCardsTest extends AbstractAdyenTestCase
{
    protected Context|MockObject $contextMock;
    protected InstallmentsManager|MockObject $installmentsManagerMock;
    protected PaymentExtensionFactory|MockObject $paymentExtensionFactoryMock;
    protected PaymentExtensionInterface|MockObject $paymentExtensionMock;
    protected Session|MockObject $sessionMock;
    protected Quote|MockObject $quoteMock;
    protected Payment|MockObject $paymentMock;
    protected PaymentInformationManagementInterface|MockObject $paymentInformationManagementMock;
    protected AdyenOrderPaymentStatusInterface|MockObject $adyenOrderPaymentStatusMock;
    protected ?StoredCards $storedCards;

    public function setUp(): void
    {
        $this->installmentsManagerMock = $this->createMock(InstallmentsManager::class);
        $this->paymentExtensionFactoryMock = $this->createGeneratedMock(
            PaymentExtensionFactory::class,
            ['create']
        );
        $this->paymentExtensionMock = $this->createGeneratedMock(
            PaymentExtensionInterface::class,
            ['setAgreementIds']
        );
        $this->sessionMock = $this->createMock(Session::class);
        $this->quoteMock = $this->createMock(Quote::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->paymentInformationManagementMock =
            $this->createMock(PaymentInformationManagementInterface::class);
        $this->adyenOrderPaymentStatusMock =
            $this->createMock(AdyenOrderPaymentStatusInterface::class);

        $this->contextMock = $this->createMock(Context::class);
        $this->contextMock->method('getSession')->willReturn($this->sessionMock);
        $this->contextMock->method('getPaymentInformationManagement')
            ->willReturn($this->paymentInformationManagementMock);
        $this->contextMock->method('getAdyenOrderPaymentStatus')
            ->willReturn($this->adyenOrderPaymentStatusMock);

        $this->storedCards = new StoredCards(
            $this->contextMock,
            $this->installmentsManagerMock,
            $this->paymentExtensionFactoryMock
        );
    }

    public function tearDown(): void
    {
        $this->storedCards = null;
    }

    public function testGetMethodCode()
    {
        $this->assertEquals(CreditCard::METHOD_CC, $this->storedCards->getMethodCode());
    }

    public function testEvaluateComplete()
    {
        $successMock = $this->createMock(Success::class);
        $resultFactoryMock = $this->createMock(EvaluationResultFactory::class);
        $resultFactoryMock->expects($this->once())
            ->method('createSuccess')
            ->willReturn($successMock);

        $this->assertInstanceOf(
            Success::class,
            $this->storedCards->evaluateCompletion($resultFactoryMock)
        );
    }

    public function testGetFormattedInstallments()
    {
        $mockInstalments = '{"visa":[1,2,3]}';

        $this->installmentsManagerMock->expects($this->once())
            ->method('getFormattedInstallments')
            ->willReturn($mockInstalments);

        $result = $this->storedCards->getFormattedInstallments();

        $this->assertIsString($result);
        $this->assertEquals($mockInstalments, $result);
    }

    public function testPlaceOrderSuccess()
    {
        $data = [
            ProcessingMetadataInterface::POST_KEY_PUBLIC_HASH => 'mock_public_hash',
            'stateData' => [],
            'extension_attributes' => [
                'agreement_id' => ['1', '2']
            ]
        ];
        $paymentStatus = 'success';
        $quoteId = '111';
        $orderId = '123';

        $this->setPlaceOrderCommonExpectations($quoteId);

        $this->paymentInformationManagementMock->expects($this->once())
            ->method('savePaymentInformationAndPlaceOrder')
            ->with($quoteId, $this->paymentMock)
            ->willReturn($orderId);

        $this->adyenOrderPaymentStatusMock->expects($this->once())
            ->method('getOrderPaymentStatus')
            ->with($orderId)
            ->willReturn($paymentStatus);

        $this->storedCards->placeOrder($data);

        $this->assertEquals($paymentStatus, $this->storedCards->paymentStatus);
    }

    private function setPlaceOrderCommonExpectations($quoteId)
    {
        $this->sessionMock->expects($this->once())
            ->method('getQuoteId')
            ->willReturn($quoteId);

        $this->sessionMock->expects($this->exactly(2))
            ->method('getQuote')
            ->willReturn($this->quoteMock);

        $this->quoteMock->expects($this->exactly(2))
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->paymentExtensionFactoryMock
            ->method('create')
            ->willReturn($this->paymentExtensionMock);
    }
}
