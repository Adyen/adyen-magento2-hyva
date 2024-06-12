<?php

declare(strict_types=1);

namespace Adyen\Hyva\Block;

use Adyen\Hyva\Magewire\Payment\Method\ApplePay;
use Adyen\Hyva\Magewire\Payment\Method\GooglePay;
use Adyen\Hyva\Magewire\Payment\Method\Klarna;
use Adyen\Hyva\Magewire\Payment\Method\Paypal;
use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;
use Magento\Framework\View\Element\Template\Context;

class Template extends \Magento\Framework\View\Element\Template
{
    const PARENT_PAYMENT_METHODS_BLOCK = 'checkout.payment.methods';
    const MAGEWIRE = 'magewire';

    private GooglePay $googlepayWire;
    private Klarna $klarnaWire;
    private ApplePay $applePayWire;
    private Paypal $paypalWire;

    public function __construct(
        GooglePay $googlepayWire,
        Klarna $klarnaWire,
        ApplePay $applePayWire,
        Paypal $paypalWire,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->googlepayWire = $googlepayWire;
        $this->klarnaWire = $klarnaWire;
        $this->applePayWire = $applePayWire;
        $this->paypalWire = $paypalWire;
    }

    public function _prepareLayout()
    {
        $this->renderAdyenPaymentMethods();

        return parent::_prepareLayout();
    }

    private function renderAdyenPaymentMethods(): void
    {
        // TODO:: Remove this sample data and obtain payment methods from main module.
        // TODO:: Create abstract class for payment method wires.
        // TODO:: Create abstract payment method template
        $samplePaymentMethods = [
            [
                'method' => 'adyen_googlepay',
                'blockName' => 'checkout.payment.method.adyen_googlepay',
                'template' => 'Adyen_Hyva::payment/method-renderer/adyen-googlepay-method.phtml',
                'wire' => $this->googlepayWire
            ],
            [
                'method' => 'adyen_klarna',
                'blockName' => 'checkout.payment.method.adyen_klarna',
                'template' => 'Adyen_Hyva::payment/method-renderer/adyen-klarna-method.phtml',
                'wire' => $this->klarnaWire
            ],
            [
                'method' => 'adyen_applepay',
                'blockName' => 'checkout.payment.method.adyen_applepay',
                'template' => 'Adyen_Hyva::payment/method-renderer/adyen-applepay-method.phtml',
                'wire' => $this->applePayWire
            ],
            [
                'method' => 'adyen_paypal',
                'blockName' => 'checkout.payment.method.adyen_paypal',
                'template' => 'Adyen_Hyva::payment/method-renderer/adyen-paypal-method.phtml',
                'wire' => $this->paypalWire
            ]
        ];

        foreach ($samplePaymentMethods as $samplePaymentMethod) {
            $this->generatePaymentMethodBlock(
                $samplePaymentMethod['method'],
                $samplePaymentMethod['blockName'],
                $samplePaymentMethod['wire'],
                $samplePaymentMethod['template']
            );
        }
    }

    private function generatePaymentMethodBlock(
        string $methodCode,
        string $blockName,
        EvaluationInterface $paymentMethodWire,
        string $template
    ): void {
        $layout = $this->getLayout();

        if (!array_key_exists($blockName, $layout->getAllBlocks())) {
            $block = $layout->createBlock(
                \Magento\Framework\View\Element\Template::class,
                $blockName
            );
            $block->setTemplate($template);
            $block->setData(self::MAGEWIRE, $paymentMethodWire);

            $layout->setChild(self::PARENT_PAYMENT_METHODS_BLOCK, $blockName, $methodCode);
        }
    }
}
