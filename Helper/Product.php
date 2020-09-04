<?php

namespace MageSuite\GoogleStructuredData\Helper;

class Product extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_CONFIG_PATH = 'structured_data/product_page';

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $config;

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) $this->getConfig()->getEnabled();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->getConfig()->getDescription();
    }

    /**
     * @return bool
     */
    public function isShowRating()
    {
        return (bool) $this->getConfig()->getShowRating();
    }

    /**
     * @return string
     */
    public function getBrand()
    {
        return $this->getConfig()->getBrand();
    }

    /**
     * @return string
     */
    public function getManufacturer()
    {
        return $this->getConfig()->getManufacturer();
    }

    /**
     * @return \Magento\Framework\DataObject
     */
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
