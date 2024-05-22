<?php

declare(strict_types=1);

namespace Adyen\Hyva\Model;

class MethodList
{
    public function __construct(
        private $availableMethods = []
    ) {
    }

    /**
     * @return array
     */
    public function collectAvailableMethods(): array
    {
        return $this->availableMethods;
    }
}
