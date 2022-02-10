<?php

namespace MageSuite\GoogleStructuredData\Helper;

class Configuration extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_BREADCRUMB_ENABLED = 'structured_data/breadcrumbs/is_enabled';
    const XML_PATH_SEARCH_BOX_ENABLED = 'structured_data/search_box/is_enabled';
    const XML_PATH_CATEGORY_PAGE_INCLUDE_PRODUCTS_ENABLED = 'structured_data/category_page/include_products';

    public function isBreadcrumbsEnabled()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_BREADCRUMB_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isSearchBoxEnabled()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_SEARCH_BOX_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isCategoryPageIncludeProducts()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_CATEGORY_PAGE_INCLUDE_PRODUCTS_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
