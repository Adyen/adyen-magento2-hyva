<?php

namespace Adyen\Hyva\Test\Unit\Plugin;

use Adyen\Hyva\Model\PaymentMethod\Filter\FilterInterface;
use Adyen\Hyva\Plugin\InitMethods;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InitMethodsTest extends TestCase
{
    /**
     * @var FilterInterface|MockObject
     */
    private $paymentMethodFilter;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var InitMethods
     */
    private $initMethods;

    /**
     * @var PaymentMethodManagementInterface|MockObject
     */
    private $subject;

    protected function setUp(): void
    {
        $this->paymentMethodFilter = $this->createMock(FilterInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->subject = $this->createMock(PaymentMethodManagementInterface::class);

        $this->initMethods = new InitMethods($this->paymentMethodFilter, $this->logger);
    }

    public function testAfterGetListIsFiltered(): void
    {
        $quoteId = 123;
        $paymentMethods = ['payment_method_1', 'payment_method_2', 'payment_method_3'];
        $filteredMethods = ['payment_method_1', 'payment_method_2'];

        $this->paymentMethodFilter->expects($this->once())
            ->method('execute')
            ->with($quoteId, $paymentMethods)
            ->willReturn($filteredMethods);

        $result = $this->initMethods->afterGetList($this->subject, $paymentMethods, $quoteId);

        $this->assertEquals($filteredMethods, $result);
    }

    public function testMethodFilterThrowsExceptionListRemainsUnchanged(): void
    {
        $quoteId = 123;
        $paymentMethods = ['payment_method_1', 'payment_method_2', 'payment_method_3'];

        $this->paymentMethodFilter->method('execute')
            ->willThrowException(new \Exception('Localized problem X.'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Error during filtering available methods: Localized problem X.');

        $result = $this->initMethods->afterGetList($this->subject, $paymentMethods, $quoteId);

        $this->assertEquals($paymentMethods, $result);
    }
}
