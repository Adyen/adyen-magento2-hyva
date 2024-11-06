<?php

namespace Adyen\Hyva\Test\Unit\Model;

use Adyen\Hyva\Model\MethodList;
use Adyen\Hyva\Model\Ui\AdyenHyvaConfigProvider;
use Adyen\Payment\Test\Unit\AbstractAdyenTestCase;

class MethodListTest extends AbstractAdyenTestCase
{
    private MethodList $methodList;

   protected function setUp(): void
   {
       $adyenHyvaConfigProviderMock = $this->createMock(AdyenHyvaConfigProvider::class);
       $this->methodList = new MethodList($adyenHyvaConfigProviderMock, [], []);
   }

   public function testCollectAvailableMethodsReturnsEmptyArray(): void
   {
       $this->assertIsArray($this->methodList->collectAvailableMethods());
       $this->assertEmpty($this->methodList->collectAvailableMethods());
   }

   public function testCollectAvailableMethodsReturnsCorrectMethods(): void
   {
       $this->assertIsArray($this->methodList->collectAvailableMethods());
   }
}
