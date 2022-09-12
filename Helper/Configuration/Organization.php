<?php

namespace MageSuite\GoogleStructuredData\Helper\Configuration;

class Organization
{
    const XML_PATH_ORGANIZATION_IS_ENABLED = 'structured_data/organization/is_enabled';
    const XML_PATH_ORGANIZATION_NAME = 'structured_data/organization/name';
    const XML_PATH_ORGANIZATION_LOGO = 'structured_data/organization/logo';
    const XML_PATH_ORGANIZATION_DESCRIPTION = 'structured_data/organization/description';
    const XML_PATH_ORGANIZATION_ADDRESS = 'structured_data/organization/address';
    const XML_PATH_ORGANIZATION_CONTACT = 'structured_data/organization/contact';

    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface)
    {
        $this->scopeConfig = $scopeConfigInterface;
    }

    public function isEnabled()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_ORGANIZATION_IS_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getName()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ORGANIZATION_NAME, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getLogo()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ORGANIZATION_LOGO, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getDescription()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ORGANIZATION_DESCRIPTION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getAddressData()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ORGANIZATION_ADDRESS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?? [];
    }

    public function getContactData()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ORGANIZATION_CONTACT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?? [];
    }
}
