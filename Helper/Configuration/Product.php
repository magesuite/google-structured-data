<?php

namespace MageSuite\GoogleStructuredData\Helper\Configuration;

class Product
{
    public const XML_CONFIG_PATH_IS_ENABLED = 'structured_data/product_page/is_enabled';
    public const XML_CONFIG_PATH_IS_INDEXING_ENABLED = 'structured_data/product_page/is_indexing_enabled';
    public const XML_CONFIG_PATH_CACHE_LIFETIME = 'structured_data/product_page/cache_lifetime';
    public const XML_CONFIG_PATH_SHOW_RATING = 'structured_data/product_page/show_rating';
    public const XML_CONFIG_PATH_ATTRIBUTES = 'structured_data/product_page/attributes';

    public const XML_CONFIG_PATH_DELIVERY_DATA_ENABLED = 'structured_data/product_page/delivery_data/is_enabled';
    public const XML_CONFIG_PATH_DELIVERY_DATA_BUSINESS_DAYS = 'structured_data/product_page/delivery_data/business_days';
    public const XML_CONFIG_PATH_DELIVERY_DATA_HANDLING_TIME_VALUE = 'structured_data/product_page/delivery_data/handling_time_value';
    public const XML_CONFIG_PATH_DELIVERY_DATA_CUTOFF_TIME_VALUE = 'structured_data/product_page/delivery_data/cutoff_time';
    public const XML_CONFIG_PATH_DELIVERY_DATA_HANDLING_TIME_UNIT_CODE = 'structured_data/product_page/delivery_data/handling_time_unit_code';
    public const XML_CONFIG_PATH_DELIVERY_DATA_TRANSIT_TIME_VALUE = 'structured_data/product_page/delivery_data/transit_time_value';
    public const XML_CONFIG_PATH_DELIVERY_DATA_TRANSIT_TIME_UNIT_CODE = 'structured_data/product_page/delivery_data/transit_time_unit_code';

    public const XML_CONFIG_PATH_GROUPED_USE_PARENT_PRODUCT_URL = 'structured_data/product_page/grouped/use_parent_product_url';
    public const XML_CONFIG_PATH_GROUPED_USE_PARENT_PRODUCT_IMAGES = 'structured_data/product_page/grouped/use_parent_product_images';
    public const XML_CONFIG_PATH_GROUPED_USE_PARENT_PRODUCT_REVIEWS = 'structured_data/product_page/grouped/use_parent_product_reviews';

    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface)
    {
        $this->scopeConfig = $scopeConfigInterface;
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_IS_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isIndexingEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_IS_INDEXING_ENABLED);
    }

    public function getCacheLifetime(): int
    {
        return $this->scopeConfig->getValue(self::XML_CONFIG_PATH_CACHE_LIFETIME);
    }

    public function shouldShowRating(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_SHOW_RATING, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isDeliveryDataEnabled(int $storeId): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_DELIVERY_DATA_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getBusinessDays(int $storeId): array
    {
        $days = $this->scopeConfig->getValue(self::XML_CONFIG_PATH_DELIVERY_DATA_BUSINESS_DAYS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);

        $businessDays = preg_split('/,/', $days);
        if ($businessDays === false) {
            $businessDays = [];
        }

        return $businessDays;
    }

    public function getCutoffTime(int $storeId): ?string
    {
        return $this->scopeConfig->getValue(self::XML_CONFIG_PATH_DELIVERY_DATA_CUTOFF_TIME_VALUE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getHandlingTime(int $storeId): ?string
    {
        return $this->scopeConfig->getValue(self::XML_CONFIG_PATH_DELIVERY_DATA_HANDLING_TIME_VALUE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getHandlingTimeUnit(int $storeId): ?string
    {
        return $this->scopeConfig->getValue(self::XML_CONFIG_PATH_DELIVERY_DATA_HANDLING_TIME_UNIT_CODE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getTransitTime(int $storeId): ?string
    {
        return $this->scopeConfig->getValue(self::XML_CONFIG_PATH_DELIVERY_DATA_TRANSIT_TIME_VALUE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getTransitTimeUnit(int $storeId): ?string
    {
        return $this->scopeConfig->getValue(self::XML_CONFIG_PATH_DELIVERY_DATA_TRANSIT_TIME_UNIT_CODE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getConfiguredAttribute($attributeCode): ?string
    {
        $attributesConfig = $this->scopeConfig->getValue(self::XML_CONFIG_PATH_ATTRIBUTES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $attributesConfig[$attributeCode] ?? null;
    }

    public function isUseParentProductUrlForGrouped(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_GROUPED_USE_PARENT_PRODUCT_URL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isUseParentProductImagesForGrouped(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_GROUPED_USE_PARENT_PRODUCT_IMAGES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isUseParentProductReviewsForGrouped(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_GROUPED_USE_PARENT_PRODUCT_REVIEWS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
