<?php

namespace Adyen\Hyva\Test\Unit\Model;

use Adyen\Hyva\Model\MethodList;

class MethodListTest extends \PHPUnit\Framework\TestCase
{
    private MethodList $methodList;

   protected function setUp(): void
   {
       $this->methodList = new MethodList();
   }

   public function testCollectAvailableMethodsReturnsEmptyArray(): void
   {
       $this->assertIsArray($this->methodList->collectAvailableMethods());
       $this->assertEmpty($this->methodList->collectAvailableMethods());
   }

   public function testCollectAvailableMethodsReturnsCorrectMethods(): void
   {
       $methods = ['method1', 'method2'];
       $this->methodList = new MethodList($methods);

       $this->assertIsArray($this->methodList->collectAvailableMethods());
       $this->assertCount(2, $this->methodList->collectAvailableMethods());
       $this->assertEquals($methods, $this->methodList->collectAvailableMethods());
   }
}
