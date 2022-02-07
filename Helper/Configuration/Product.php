<?php

namespace MageSuite\GoogleStructuredData\Helper\Configuration;

class Product extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_CONFIG_PATH = 'structured_data/product_page';

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $config;

    public function isEnabled()
    {
        return (bool)$this->getConfig()->getEnabled();
    }

    public function isShowRating()
    {
        return (bool)$this->getConfig()->getShowRating();
    }

    public function getConfiguredAttribute($attributeCode)
    {
        return $this->getConfig()->getData($attributeCode);
    }

    protected function getConfig()
    {
        if ($this->config === null) {
            $this->config = new \Magento\Framework\DataObject(
                $this->scopeConfig->getValue(self::XML_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            );
        }

        return $this->config;
    }
}
