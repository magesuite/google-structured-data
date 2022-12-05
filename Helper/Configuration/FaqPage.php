<?php
declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Helper\Configuration;

class FaqPage
{
    const XML_PATH_FAQ_PAGE_IS_ENABLED = 'structured_data/faq_page/is_enabled';

    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnabled($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_FAQ_PAGE_IS_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
