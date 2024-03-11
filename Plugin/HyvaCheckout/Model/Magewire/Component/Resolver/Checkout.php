<?php

namespace Adyen\Hyva\Plugin\HyvaCheckout\Model\Magewire\Component\Resolver;

use Adyen\Hyva\Api\ProcessingMetadataInterface;

use Adyen\Hyva\Model\CreditCard\StoredCardsManager;
use Hyva\Checkout\Model\Magewire\Component\Resolver\Checkout as Subject;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\Page;
use Magewirephp\Magewire\Model\RequestInterface;
use Psr\Log\LoggerInterface;

class Checkout
{
    public function __construct(
        private readonly LayoutInterface $layout,
        private readonly StoredCardsManager $storedCardsManager,
        private readonly LoggerInterface $logger
    ) {
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
                if ($registeredMagewire = $this->storedCardsManager->getMagewireComponent($request->getFingerprint('name'))) {
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
            $this->logger->error('Could not create block for vault stored cards and add it to layout: ' . $exception->getMessage());
        }
    }
}
