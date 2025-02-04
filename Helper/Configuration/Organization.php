<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Helper\Configuration;

class Organization
{
    public const XML_PATH_ORGANIZATION_IS_ENABLED = 'structured_data/organization/is_enabled';
    public const XML_PATH_ORGANIZATION_NAME = 'structured_data/organization/name';
    public const XML_PATH_ORGANIZATION_LOGO = 'structured_data/organization/logo';
    public const XML_PATH_ORGANIZATION_DESCRIPTION = 'structured_data/organization/description';
    public const XML_PATH_ORGANIZATION_ADDRESS = 'structured_data/organization/address';
    public const XML_PATH_ORGANIZATION_CONTACT = 'structured_data/organization/contact';

    public const XML_PATH_ORGANIZATION_RETURN_POLICY_ENABLED = 'structured_data/organization/return_policy/is_enabled';
    public const XML_PATH_ORGANIZATION_RETURN_POLICY_RETURN_POLICY_CATEGORY = 'structured_data/organization/return_policy/return_policy_category';
    public const XML_PATH_ORGANIZATION_RETURN_POLICY_RETURN_DAYS = 'structured_data/organization/return_policy/return_days';
    public const XML_PATH_ORGANIZATION_RETURN_POLICY_RETURN_POLICY_LINK = 'structured_data/organization/return_policy/return_policy_link';

    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface)
    {
        $this->scopeConfig = $scopeConfigInterface;
    }

    public function isEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_ORGANIZATION_IS_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getName(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ORGANIZATION_NAME, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getLogo(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ORGANIZATION_LOGO, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getDescription(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ORGANIZATION_DESCRIPTION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getAddressData(): array
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ORGANIZATION_ADDRESS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?? [];
    }

    public function getContactData(): array
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ORGANIZATION_CONTACT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?? [];
    }

    public function isReturnPolicyEnabled(int $storeId): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ORGANIZATION_RETURN_POLICY_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getReturnPolicyCategory(int $storeId): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ORGANIZATION_RETURN_POLICY_RETURN_POLICY_CATEGORY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getReturnDays(int $storeId): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_ORGANIZATION_RETURN_POLICY_RETURN_DAYS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getReturnPolicyLink(int $storeId): ?string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ORGANIZATION_RETURN_POLICY_RETURN_POLICY_LINK, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }
}
