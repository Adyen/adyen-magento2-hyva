<?php

namespace Adyen\Hyva\Test\Unit\Plugin\ViewModel\Checkout\Component\Payment;

use Adyen\Hyva\Plugin\ViewModel\Checkout\Payment\MethodList;
use Adyen\Payment\Helper\PaymentMethodsFilter;
use Adyen\Payment\Test\Unit\AbstractAdyenTestCase;
use Hyva\Checkout\ViewModel\Checkout\Payment\MethodList as HyvaMethodList;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Model\Method\Adapter;
use Magento\Quote\Api\Data\CartInterface;
use PHPUnit\Framework\MockObject\MockObject;

class MethodListTest extends AbstractAdyenTestCase
{
    protected ?MethodList $methodList;
    protected CheckoutSession|MockObject $checkoutSessionMock;
    protected PaymentMethodsFilter|MockObject $paymentMethodsFilterHelperMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->checkoutSessionMock = $this->createMock(CheckoutSession::class);
        $this->paymentMethodsFilterHelperMock = $this->createMock(PaymentMethodsFilter::class);

        $this->methodList = new MethodList(
            $this->checkoutSessionMock,
            $this->paymentMethodsFilterHelperMock
        );
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->methodList = null;
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testAfterGetListSuccess()
    {
        $subjectMock = $this->createMock(HyvaMethodList::class);
        $resultMock = [
            0 => [$this->createMock(Adapter::class)],
            1 => []
        ];

        $cartMock = $this->createMock(CartInterface::class);
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($cartMock);

        $this->paymentMethodsFilterHelperMock->expects($this->once())
            ->method('sortAndFilterPaymentMethods')
            ->with($resultMock, $cartMock)
            ->willReturn($resultMock);

        $methodResult = $this->methodList->afterGetList($subjectMock, $resultMock);
        $this->assertEquals($resultMock[0], $methodResult);
    }
}
