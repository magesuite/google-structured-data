<?php

namespace MageSuite\GoogleStructuredData\Helper;

class Organization extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_ORGANIZATION = 'structured_data/organization';

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $config;

    public function isEnabled()
    {
        return (bool) $this->getConfig()->getEnabled();
    }

    public function getName()
    {
        return $this->getConfig()->getName();
    }

    public function getLogo()
    {
        return $this->getConfig()->getLogo();
    }

    public function getDescription()
    {
        return $this->getConfig()->getDescription();
    }

    public function getCountry()
    {
        return $this->getConfig()->getCountry();
    }

    public function getRegion()
    {
        return $this->getConfig()->getRegion();
    }

    public function getPostal()
    {
        return $this->getConfig()->getPostal();
    }

    public function getCity()
    {
        return $this->getConfig()->getCity();
    }

    public function getSales()
    {
        return $this->getConfig()->getSales();
    }

    public function getTechnical()
    {
        return $this->getConfig()->getTechnical();
    }

    public function getCustomerService()
    {
        return $this->getConfig()->getCustomerService();
    }

    protected function getConfig()
    {
        if ($this->config === null) {
            $config = $this->scopeConfig->getValue(self::XML_PATH_ORGANIZATION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $this->config = new \Magento\Framework\DataObject($config);
        }

        return $this->config;
    }
}
