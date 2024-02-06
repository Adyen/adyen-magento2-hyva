<?php

namespace Adyen\Hyva\Block;

use Magento\Framework\View\Element\Template;
use Adyen\Hyva\Model\MethodList;

class HyvaCheckout extends Template
{
    private MethodList $methodList;

    public function __construct(
        Template\Context $context,
        MethodList $methodList,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->methodList = $methodList;
    }

    /**
     * @return array
     */
    public function getSupportedMethods(): array
    {
        return array_values($this->methodList->collectAvailableMethods());
    }
}
