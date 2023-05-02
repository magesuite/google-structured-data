<?php

namespace MageSuite\GoogleStructuredData\Helper\Configuration;

class Category
{
    const XML_PATH_CATEGORY_PAGE_INCLUDE_PRODUCTS_ENABLED = 'structured_data/category_page/include_products';
    const XML_PATH_CATEGORY_PAGE_SHOW_RATING = 'structured_data/category_page/show_rating';

    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface)
    {
        $this->scopeConfig = $scopeConfigInterface;
    }

    public function isCategoryPageIncludeProducts(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_CATEGORY_PAGE_INCLUDE_PRODUCTS_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isShowRating(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_CATEGORY_PAGE_SHOW_RATING, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
