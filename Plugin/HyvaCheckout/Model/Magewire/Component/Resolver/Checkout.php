<?php

namespace Adyen\Hyva\Plugin\HyvaCheckout\Model\Magewire\Component\Resolver;

use Adyen\Hyva\Api\ProcessingMetadataInterface;

use Adyen\Hyva\Model\CreditCard\SavedCardsManager;
use Hyva\Checkout\Model\Magewire\Component\Resolver\Checkout as Subject;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\Page;
use Magewirephp\Magewire\Model\RequestInterface;

class Checkout
{
    private LayoutInterface $layout;
    private SavedCardsManager $savedCardsManager;

    public function __construct(
        LayoutInterface $layout,
        SavedCardsManager $savedCardsManager
    ) {
        $this->layout = $layout;
        $this->savedCardsManager = $savedCardsManager;
    }

    /**
     * @param Subject $subject
     * @param callable $proceed
     * @param Page $page
     * @param RequestInterface $request
     * @return void
     */
    public function aroundProcessComponentRequest(Subject $subject, callable $proceed, Page $page, RequestInterface $request)
    {
        $proceed($page, $request);

        try {
            if (str_contains($request->getFingerprint('name'), ProcessingMetadataInterface::VAULT_LAYOUT_PREFIX)
                && $page->getLayout()->getBlock($request->getFingerprint('name')) == null
            ) {
                if ($registeredMagewire = $this->savedCardsManager->getMagewireComponent($request->getFingerprint('name'))) {
                    $paymentBlock = $page->getLayout()->createBlock(
                        \Magento\Framework\View\Element\Template::class,
                        $registeredMagewire->getName(),
                        [
                            'data' => [
                                ProcessingMetadataInterface::BLOCK_PROPERTY_MAGEWIRE => $registeredMagewire->getMagewire(),
                                ProcessingMetadataInterface::BLOCK_PROPERTY_STORED_CARD => $registeredMagewire->getStoredCard()
                            ]
                        ]
                    )->setData('area', 'frontend')->setTemplate($registeredMagewire->getTemplate());

                    $page->getLayout()->addBlock($paymentBlock);
                }
            }
        } catch (\Exception $exception) {
            ;
        }
    }
}
