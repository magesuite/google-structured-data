<?php

namespace MageSuite\GoogleStructuredData\Helper;

class Configuration
{
    public const XML_PATH_BREADCRUMB_ENABLED = 'structured_data/breadcrumbs/is_enabled';
    public const XML_PATH_SEARCH_BOX_ENABLED = 'structured_data/search_box/is_enabled';
    public const COUNTRY_CODE_PATH = 'general/country/default';
    public const TIMEZONE_PATH = 'general/locale/timezone';

    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface)
    {
        $this->scopeConfig = $scopeConfigInterface;
    }

    public function isBreadcrumbsEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_BREADCRUMB_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isSearchBoxEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_SEARCH_BOX_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getCountryByWebsite(\Magento\Store\Api\Data\WebsiteInterface $website): string
    {
        return $this->scopeConfig->getValue(
            self::COUNTRY_CODE_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES,
            $website
        ) ?? '';
    }

    public function getTimezone(\Magento\Store\Api\Data\WebsiteInterface $website): string
    {
        return $this->scopeConfig->getValue(
            self::TIMEZONE_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES,
            $website
        ) ?? '';
    }

    public function getCarriers(\Magento\Store\Api\Data\StoreInterface $store): array
    {
        return $this->scopeConfig->getValue(
            'carriers',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) ?: [];
    }
}
