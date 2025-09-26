<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Helper\Configuration;

class Category
{
    public const XML_PATH_CATEGORY_PAGE_INCLUDE_PRODUCTS_ENABLED = 'structured_data/category_page/include_products';
    public const XML_PATH_CATEGORY_PAGE_INCLUDE_LIST_ITEM_ENABLED = 'structured_data/category_page/include_list_item';
    public const XML_PATH_CATEGORY_PAGE_SHOW_RATING = 'structured_data/category_page/show_rating';

    public function __construct(
        protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {}

    public function doesCategoryPageIncludeProducts(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CATEGORY_PAGE_INCLUDE_PRODUCTS_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isCategoryPageIncludeListItem(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CATEGORY_PAGE_INCLUDE_LIST_ITEM_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function shouldShowRating(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CATEGORY_PAGE_SHOW_RATING, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
