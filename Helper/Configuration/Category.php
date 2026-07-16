<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Helper\Configuration;

class Category
{
    public const XML_PATH_CATEGORY_PAGE_INCLUDE_PRODUCTS = 'structured_data/category_page/include_products';
    public const XML_PATH_CATEGORY_PAGE_INCLUDE_LIST_ITEM = 'structured_data/category_page/include_list_item';
    public const XML_PATH_CATEGORY_PAGE_SHOW_RATING = 'structured_data/category_page/show_rating';

    public function __construct(
        protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {}

    public function doesCategoryPageIncludeProducts(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CATEGORY_PAGE_INCLUDE_PRODUCTS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getListItemMode(): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_CATEGORY_PAGE_INCLUDE_LIST_ITEM, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isListItemEnabled(): bool
    {
        return $this->getListItemMode() !== \MageSuite\GoogleStructuredData\Model\Config\Source\CategoryListItemMode::MODE_DISABLED;
    }

    public function shouldEmbedFullProductData(): bool
    {
        return $this->getListItemMode() === \MageSuite\GoogleStructuredData\Model\Config\Source\CategoryListItemMode::MODE_FULL_PRODUCT_DATA;
    }

    public function shouldShowRating(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CATEGORY_PAGE_SHOW_RATING, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
