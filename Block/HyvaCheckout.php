<?php

declare(strict_types=1);

namespace Adyen\Hyva\Block;

use Magento\Framework\View\Element\Template;
use Adyen\Hyva\Model\MethodList;

class HyvaCheckout extends Template
{
    public function __construct(
        Template\Context $context,
        private MethodList $methodList,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getSupportedMethods(): array
    {
        return array_values($this->methodList->collectAvailableMethods());
    }
}
