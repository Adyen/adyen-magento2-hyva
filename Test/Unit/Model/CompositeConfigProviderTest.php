<?php
/**
 *
 * Adyen Payment module (https://www.adyen.com/)
 *
 * Copyright (c) 2024 Adyen N.V. (https://www.adyen.com/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>
 */

namespace Adyen\Hyva\Test\Unit\Model;

use Adyen\Hyva\Model\CompositeConfigProvider;
use Adyen\Payment\Test\Unit\AbstractAdyenTestCase;
use Magento\Checkout\Model\ConfigProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;

class CompositeConfigProviderTest extends AbstractAdyenTestCase
{
    protected ?CompositeConfigProvider $compositeConfigProvider;
    protected MockObject|array $configProvidersMock;
    protected MockObject|ConfigProviderInterface $singleConfigProviderMock;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->singleConfigProviderMock = $this->createMock(ConfigProviderInterface::class);
        $this->configProvidersMock[] = $this->singleConfigProviderMock;

        $this->compositeConfigProvider = new CompositeConfigProvider($this->configProvidersMock);
    }

    /**
     * @return void
     */
    public function tearDown(): void
    {
        $this->compositeConfigProvider = null;
    }

    /**
     * Test case to assert all available config values provided through di.xml
     *
     * @return void
     */
    public function testGetConfig()
    {
        $configMock = [
            'config1' => 'value1',
            'config2' => 'value2'
        ];

        $this->singleConfigProviderMock->expects($this->once())
            ->method('getConfig')
            ->willReturn($configMock);

        $result = $this->compositeConfigProvider->getConfig();
        $this->assertEquals($configMock, $result);
    }
}
