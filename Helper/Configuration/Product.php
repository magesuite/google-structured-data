<?php

namespace MageSuite\GoogleStructuredData\Helper\Configuration;

class Product
{
    const XML_CONFIG_PATH_IS_ENABLED = 'structured_data/product_page/is_enabled';
    const XML_CONFIG_PATH_SHOW_SATING = 'structured_data/product_page/show_rating';
    const XML_CONFIG_PATH_ATTRIBUTES = 'structured_data/product_page/attributes';
    const XML_CONFIG_PATH_GROUPED_USE_PARENT_PRODUCT_URL = 'structured_data/product_page/grouped/use_parent_product_url';
    const XML_CONFIG_PATH_GROUPED_USE_PARENT_PRODUCT_IMAGES = 'structured_data/product_page/grouped/use_parent_product_images';
    const XML_CONFIG_PATH_GROUPED_USE_PARENT_PRODUCT_REVIEWS = 'structured_data/product_page/grouped/use_parent_product_reviews';

    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface)
    {
        $this->scopeConfig = $scopeConfigInterface;
    }

    public function isEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_CONFIG_PATH_IS_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isShowRating(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_CONFIG_PATH_SHOW_SATING, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getConfiguredAttribute($attributeCode): ?string
    {
        $attributesConfig = $this->scopeConfig->getValue(self::XML_CONFIG_PATH_ATTRIBUTES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $attributesConfig[$attributeCode] ?? null;
    }

    public function isUseParentProductUrlForGrouped(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_CONFIG_PATH_GROUPED_USE_PARENT_PRODUCT_URL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isUseParentProductImagesForGrouped(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_CONFIG_PATH_GROUPED_USE_PARENT_PRODUCT_IMAGES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isUseParentProductReviewsForGrouped(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_CONFIG_PATH_GROUPED_USE_PARENT_PRODUCT_REVIEWS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
