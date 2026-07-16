<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Helper;

class Configuration
{
    public const XML_PATH_BREADCRUMB_ENABLED = 'structured_data/breadcrumbs/is_enabled';
    public const XML_PATH_BREADCRUMB_INCLUDE_HOMEPAGE = 'structured_data/breadcrumbs/include_homepage';
    public const XML_PATH_WEBSITE_ENABLED = 'structured_data/website/is_enabled';
    public const XML_PATH_WEBSITE_NAME = 'structured_data/website/name';
    public const XML_PATH_SEARCH_BOX_ENABLED = 'structured_data/website/search_box_enabled';
    public const XML_PATH_ID_USE_ROOT_DOMAIN = 'structured_data/general/id_use_root_domain';
    public const COUNTRY_CODE_PATH = 'general/country/default';
    public const TIMEZONE_PATH = 'general/locale/timezone';
    public const LOCALE_CODE_PATH = 'general/locale/code';

    public function __construct(
        protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {}

    public function isBreadcrumbsEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_BREADCRUMB_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isBreadcrumbHomepageIncluded(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_BREADCRUMB_INCLUDE_HOMEPAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getWebsiteName(): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_WEBSITE_NAME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isWebsiteEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_WEBSITE_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isSearchBoxEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SEARCH_BOX_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isIdUseRootDomain(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ID_USE_ROOT_DOMAIN,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getEntityIdBase(): string
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();

        if (!$this->isIdUseRootDomain()) {
            return $baseUrl;
        }

        return $this->buildRootDomainUrl($baseUrl);
    }

    protected function buildRootDomainUrl(string $baseUrl): string
    {
        $uri = \Laminas\Uri\UriFactory::factory($baseUrl);

        if (!$uri->getScheme() || !$uri->getHost()) {
            return $baseUrl;
        }

        $uri->setPath('/');
        $uri->setQuery(null);
        $uri->setFragment(null);

        return $uri->toString();
    }

    public function getLocale(): string
    {
        $locale = (string)$this->scopeConfig->getValue(
            self::LOCALE_CODE_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return str_replace('_', '-', $locale);
    }

    public function getCountryByWebsite(\Magento\Store\Api\Data\WebsiteInterface $website): string
    {
        return (string)$this->scopeConfig->getValue(self::COUNTRY_CODE_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES, $website);
    }

    public function getTimezone(?\Magento\Store\Api\Data\WebsiteInterface $website = null): string
    {
        return (string)$this->scopeConfig->getValue(self::TIMEZONE_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES, $website);
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
