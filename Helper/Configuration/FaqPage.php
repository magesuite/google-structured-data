<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Helper\Configuration;

class FaqPage
{
    public const XML_PATH_FAQ_PAGE_IS_ENABLED = 'structured_data/faq_page/is_enabled';
    public const XML_PATH_FAQ_PAGE_IS_ENABLED_ON_CMS_PAGE = 'structured_data/faq_page/is_enabled_on_cms_page';
    public const XML_PATH_FAQ_PAGE_IS_ENABLED_ON_CATEGORY = 'structured_data/faq_page/is_enabled_on_category';
    public const XML_PATH_FAQ_PAGE_IS_ENABLED_ON_PRODUCT = 'structured_data/faq_page/is_enabled_on_product';

    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_FAQ_PAGE_IS_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isEnabledOnCmsPage(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_FAQ_PAGE_IS_ENABLED_ON_CMS_PAGE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isEnabledOnCategory(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_FAQ_PAGE_IS_ENABLED_ON_CATEGORY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isEnabledOnProduct(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_FAQ_PAGE_IS_ENABLED_ON_PRODUCT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }
}
