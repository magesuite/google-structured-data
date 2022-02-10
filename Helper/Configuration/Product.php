<?php

namespace MageSuite\GoogleStructuredData\Helper\Configuration;

class Product extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_CONFIG_PATH_IS_ENABLED = 'structured_data/product_page/is_enabled';
    const XML_CONFIG_PATH_SHOW_SATING = 'structured_data/product_page/show_rating';
    const XML_CONFIG_PATH_ATTRIBUTES = 'structured_data/product_page/attributes';

    public function isEnabled()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_CONFIG_PATH_IS_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isShowRating()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_CONFIG_PATH_SHOW_SATING, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getConfiguredAttribute($attributeCode)
    {
        $attributesConfig = $this->scopeConfig->getValue(self::XML_CONFIG_PATH_ATTRIBUTES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $attributesConfig[$attributeCode] ?? null;
    }
}
