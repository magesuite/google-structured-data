<?php

namespace MageSuite\GoogleStructuredData\Helper;

class Configuration
{
    const XML_PATH_BREADCRUMB_ENABLED = 'structured_data/breadcrumbs/is_enabled';
    const XML_PATH_SEARCH_BOX_ENABLED = 'structured_data/search_box/is_enabled';
    const XML_PATH_CATEGORY_PAGE_INCLUDE_PRODUCTS_ENABLED = 'structured_data/category_page/include_products';

    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface)
    {
        $this->scopeConfig = $scopeConfigInterface;
    }

    public function isBreadcrumbsEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_BREADCRUMB_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isSearchBoxEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_SEARCH_BOX_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isCategoryPageIncludeProducts(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_CATEGORY_PAGE_INCLUDE_PRODUCTS_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
