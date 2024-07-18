<?php

declare(strict_types=1);

namespace Adyen\Hyva\Block;

use Adyen\Hyva\Magewire\Payment\Method\AbstractPaymentMethodWire;
use Adyen\Hyva\Model\MethodList;
use Adyen\Hyva\Model\PaymentMethodBlock;
use Adyen\Hyva\Model\PaymentMethodBlockFactory;
use Adyen\Hyva\Model\Ui\AdyenHyvaConfigProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Adyen\Hyva\Magewire\Payment\Method\AbstractPaymentMethodWireFactory;

class Template extends \Magento\Framework\View\Element\Template
{
    const PARENT_PAYMENT_METHODS_BLOCK = 'checkout.payment.methods';
    const DEFAULT_ADYEN_PAYMENT_METHOD_TEMPLATE = 'adyen-default-method.phtml';
    const TEMPLATE_DIR = 'Adyen_Hyva::payment/method-renderer';
    const MAGEWIRE = 'magewire';

    /**
     * @var AdyenHyvaConfigProvider
     */
    private AdyenHyvaConfigProvider $adyenHyvaConfigProvider;

    /**
     * @var PaymentMethodBlockFactory
     */
    private PaymentMethodBlockFactory $paymentMethodBlockFactory;

    /**
     * @var AbstractPaymentMethodWireFactory
     */
    private AbstractPaymentMethodWireFactory $paymentMethodWireFactory;

    /**
     * @var MethodList
     */
    private MethodList $methodList;

    public function __construct(
        AdyenHyvaConfigProvider $adyenHyvaConfigProvider,
        PaymentMethodBlockFactory $paymentMethodBlockFactory,
        AbstractPaymentMethodWireFactory $paymentMethodWireFactory,
        Context $context,
        MethodList $methodList,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->adyenHyvaConfigProvider = $adyenHyvaConfigProvider;
        $this->paymentMethodBlockFactory = $paymentMethodBlockFactory;
        $this->paymentMethodWireFactory = $paymentMethodWireFactory;
        $this->methodList = $methodList;
    }

    /**
     * Overrides parent method to include Adyen payment methods
     *
     * @return Template
     * @throws LocalizedException
     */
    public function _prepareLayout()
    {
        $this->renderAdyenPaymentMethods();

        return parent::_prepareLayout();
    }

    /**
     * Render available payment methods
     *
     * @return void
     * @throws LocalizedException
     */
    private function renderAdyenPaymentMethods(): void
    {
        $methods = $this->methodList->collectAvailableMethods();
        /** @var PaymentMethodBlock[] $paymentMethodBlocks */
        $paymentMethodBlocks = [];

        foreach ($methods as $method) {
            /** @var PaymentMethodBlock $paymentMethodBlock */
            $paymentMethodBlock = $this->paymentMethodBlockFactory->create();

            if ($this->methodList->getCustomMagewireClass($method)) {
                $paymentMethodBlock->setWire($this->methodList->getCustomMagewireClass($method));
            } else {
                /** @var AbstractPaymentMethodWire $paymentMethodWire */
                $paymentMethodWire = $this->paymentMethodWireFactory->create();
                $paymentMethodWire->setMethodCode($method);

                $paymentMethodBlock->setWire($paymentMethodWire);
            }

            $paymentMethodBlock->setMethodName($method);
            $paymentMethodBlock->setBlockName(
                $this->generateBlockNameFromPaymentMethodName($method)
            );
            $paymentMethodBlock->setTemplate(
                $this->getPaymentMethodTemplate($method)
            );

            $paymentMethodBlocks[] = $paymentMethodBlock;
        }

        foreach ($paymentMethodBlocks as $paymentMethodBlock) {
            $this->createPaymentMethodBlock($paymentMethodBlock);
        }
    }

    /**
     * Creates the payment method block in the checkout layout
     *
     * @param PaymentMethodBlock $paymentMethodBlock
     * @return void
     * @throws LocalizedException
     */
    private function createPaymentMethodBlock(PaymentMethodBlock $paymentMethodBlock): void
    {
        $layout = $this->getLayout();

        if (!array_key_exists($paymentMethodBlock->getBlockName(), $layout->getAllBlocks())) {
            $block = $layout->createBlock(
                \Magento\Framework\View\Element\Template::class,
                $paymentMethodBlock->getBlockName()
            );
            $block->setTemplate($paymentMethodBlock->getTemplate());
            $block->setData(self::MAGEWIRE, $paymentMethodBlock->getWire());

            $layout->setChild(
                self::PARENT_PAYMENT_METHODS_BLOCK,
                $paymentMethodBlock->getBlockName(),
                $paymentMethodBlock->getMethodName()
            );
        }
    }

    /**
     * Generates the payment method block name from the given method name
     *
     * @param string $paymentMethodName
     * @return string
     */
    private function generateBlockNameFromPaymentMethodName(string $paymentMethodName): string
    {
        return sprintf('%s.%s', self::PARENT_PAYMENT_METHODS_BLOCK, $paymentMethodName);
    }

    /**
     * Builds template file depending on the custom renderer requirement.
     *
     * @param string $paymentMethodName
     * @return string
     */
    private function getPaymentMethodTemplate(string $paymentMethodName): string
    {
        if ($this->adyenHyvaConfigProvider->isCustomRendererRequired($paymentMethodName)) {
            $template = $this->methodList->getCustomMethodRenderer($paymentMethodName);
        } else {
            $template = self::DEFAULT_ADYEN_PAYMENT_METHOD_TEMPLATE;
        }

        return sprintf('%s/%s', self::TEMPLATE_DIR, $template);
    }
}
