<?php

namespace Adyen\Hyva\Test\Unit\Model;

use Adyen\Hyva\Model\Configuration;
use Adyen\Payment\Test\Unit\AbstractAdyenTestCase;
use Magento\Checkout\Model\CompositeConfigProvider;
use Magento\Framework\DataObjectFactory;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\MockObject\MockObject;

class ConfigurationTest extends AbstractAdyenTestCase
{
    private Configuration $configuration;
    private CompositeConfigProvider|MockObject $configProvider;
    private DataObjectFactory|MockObject $dataObjectFactory;
    private LoggerInterface|MockObject $logger;

    protected function setUp(): void
    {
        $configValues = [
            'payment' =>
                [
                    'adyenCc' => ['isCardRecurringEnabled' => 1],
                    'adyen' => ['locale' => 'es_ES']
                ]
        ];

        $this->configProvider = $this->getMockBuilder(CompositeConfigProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectFactory = $this->createGeneratedMock(DataObjectFactory::class, ['create']);
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configProvider->expects($this->exactly(2))
            ->method('getConfig')
            ->willReturn($configValues);

        $this->dataObjectFactory->expects($this->once())
            ->method('create')
            ->willReturnCallback(function ($data) {
                return new \Magento\Framework\DataObject($data['data']);
            });

        $this->configuration = new Configuration(
            $this->configProvider,
            $this->dataObjectFactory,
            $this->logger
        );
    }

    public function testGetValue(): void
    {
        $result = $this->configuration->getValue('adyenCc/isCardRecurringEnabled');
        $this->assertSame(1, $result);

        $result = $this->configuration->getValue('adyen/locale');
        $this->assertSame('es_ES', $result);

        $result = $this->configuration->getValue('nonexistent/path');
        $this->assertNull($result);
    }

    public function testGetJsonValue(): void
    {
        $jsonValue = $this->configuration->getJsonValue('adyenCc/isCardRecurringEnabled');
        $this->assertSame('1', $jsonValue);

        $jsonValue = $this->configuration->getJsonValue('adyen/locale');
        $this->assertSame('"es_ES"', $jsonValue);

        $jsonValue = $this->configuration->getJsonValue('nonexistent/path');
        $this->assertSame('null', $jsonValue);
    }
    public function testIsCCEnableStoreDetails(): void
    {
        $result = $this->configuration->isCCEnableStoreDetails(false);
        $this->assertTrue($result);

        $result = $this->configuration->isCCEnableStoreDetails(true);
        $this->assertFalse($result);
    }
}
