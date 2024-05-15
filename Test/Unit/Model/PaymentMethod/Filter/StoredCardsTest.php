<?php

namespace Adyen\Hyva\Test\Unit\Model\PaymentMethod\Filter;

use Adyen\Hyva\Model\CreditCard\StoredCardsManager;
use Adyen\Hyva\Model\PaymentMethod\Filter\StoredCards;
use PHPUnit\Framework\MockObject\MockObject;

class StoredCardsTest extends \PHPUnit\Framework\TestCase
{
    private MockObject $adyenCc;
    private MockObject $adyenGPay;
    private MockObject $adyenCcVault;
    private MockObject $storedCardsManager;

    private StoredCards $storedCards;

    public function setUp(): void
    {
        $this->adyenCc = $this->getMockBuilder(\Magento\Quote\Api\Data\PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode', 'getTitle'])
            ->getMock();
        $this->adyenGPay = $this->getMockBuilder(\Magento\Quote\Api\Data\PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode', 'getTitle'])
            ->getMock();
        $this->adyenCcVault = $this->getMockBuilder(\Magento\Quote\Api\Data\PaymentMethodInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode', 'getTitle'])
            ->getMock();

        $this->storedCardsManager = $this->createMock(StoredCardsManager::class);

        $this->storedCards = new StoredCards($this->storedCardsManager);
    }

    public function testExecuteWhenNoStoredCardsAreNotFound()
    {
        $supportedMethods = [
            'adyen_cc',
            'adyen_googlepay',
        ];

        $this->adyenCcVault->expects($this->once())->method('getCode')->willReturn('adyen_cc_vault');

        $this->storedCardsManager->expects($this->once())
            ->method('getStoredCards')
            ->willReturn([]);

        $result = $this->storedCards->execute(123, $this->getList());

        $this->assertEquals(
            $supportedMethods,
            array_keys($result)
        );

        $this->assertArrayNotHasKey('adyen_cc_vault', array_keys($result));
    }

    public function testExecuteWhenNoStoredCardsAreFound()
    {
        $supportedMethods = [
            'adyen_cc',
            'adyen_googlepay',
            'adyen_cc_vault',
        ];

        $this->adyenCcVault->expects($this->never())->method('getCode')->willReturn('adyen_cc_vault');

        $this->storedCardsManager->expects($this->once())
            ->method('getStoredCards')
            ->willReturn(['vaultCardA', 'vaultCardB']);

        $result = $this->storedCards->execute(123, $this->getList());

        $this->assertEquals(
            $supportedMethods,
            array_keys($result)
        );
    }

    private function getList(): array
    {
        return [
            'adyen_cc' => $this->adyenCc,
            'adyen_googlepay' => $this->adyenGPay,
            'adyen_cc_vault' => $this->adyenCcVault
        ];
    }
}
