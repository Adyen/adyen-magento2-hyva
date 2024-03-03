<?php

namespace Adyen\Hyva\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class ThemeConfiguration
{
    public function __construct(
        private ScopeConfigInterface $scopeConfig,
        private StoreManagerInterface $storeManager,
        private ThemeProviderInterface $themeProvider,
    ) {
    }
    public function isHyvaThemeActive(): bool
    {
        if (str_contains($this->getThemeCode(), 'Hyva')) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getThemeCode(): string
    {
        $themeId = $this->scopeConfig->getValue(
            DesignInterface::XML_PATH_THEME_ID,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );

        /** @var $theme ThemeInterface */
        $theme = $this->themeProvider->getThemeById($themeId);

        return $theme->getCode();
    }
}
