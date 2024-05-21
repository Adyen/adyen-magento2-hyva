<?php

namespace Adyen\Hyva\Test\Unit\Model\CreditCard;

use Adyen\Hyva\Model\CreditCard\InstallmentsManager;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;

class InstallmentsManagerTest extends \PHPUnit\Framework\TestCase
{
    private MockObject $session;
    private MockObject $installmentsHelper;
    private MockObject $configHelper;
    private MockObject $storeManager;
    private MockObject $adyenHelper;
    private MockObject $adyenLogger;

    private InstallmentsManager $installmentsManager;

    public function setUp(): void
    {
        $this->session = $this->createMock(\Magento\Checkout\Model\Session::class);
        $this->installmentsHelper = $this->createMock(\Adyen\Payment\Helper\Installments::class);
        $this->configHelper = $this->createMock(\Adyen\Payment\Helper\Config::class);
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->adyenHelper = $this->createMock(\Adyen\Payment\Helper\Data::class);
        $this->adyenLogger = $this->createMock(\Adyen\Payment\Logger\AdyenLogger::class);

        $this->installmentsManager = new InstallmentsManager(
            $this->session,
            $this->installmentsHelper,
            $this->configHelper,
            $this->storeManager,
            $this->adyenHelper,
            $this->adyenLogger
        );
    }

    public function testGetFormattedInstallments()
    {
        $grandTotal = '100.05';
        $quoteData = ['grand_total' => $grandTotal];
        $quote = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()->getMock();
        $store = $this->getMockBuilder(Store::class)->disableOriginalConstructor()->getMock();
        $storeId = 15;
        $configData = '{}';
        $ccTypes = ['mc', 'visa'];
        $result = '{}';

        $this->session->expects($this->once())
            ->method('getQuote')
            ->willReturn($quote);

        $quote->expects($this->once())
            ->method('getData')
            ->willReturn($quoteData);

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($store);

        $store->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);

        $this->configHelper->expects($this->once())
            ->method('getAdyenCcConfigData')
            ->with('installments', $storeId)
            ->willReturn($configData);

        $this->adyenHelper->expects($this->once())
            ->method('getAdyenCcTypes')
            ->willReturn($ccTypes);

        $this->installmentsHelper->expects($this->once())
            ->method('formatInstallmentsConfig')
            ->with($configData, $ccTypes, $grandTotal)
            ->willReturn($result);

        $this->assertEquals($result, $this->installmentsManager->getFormattedInstallments());
    }
}
