<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Helper\Configuration;

class Social
{
    public const XML_PATH_SOCIAL_IS_ENABLED = 'structured_data/organization/social/is_enabled';
    public const XML_PATH_SOCIAL_PROFILES = 'structured_data/organization/social/profiles';

    public function __construct(
        protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {}

    public function getSocialProfiles(): array
    {
        if (!$this->isEnabled()) {
            return [];
        }

        $profilesConfig = $this->scopeConfig->getValue(self::XML_PATH_SOCIAL_PROFILES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?? [];

        return array_values(array_filter($profilesConfig));
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SOCIAL_IS_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
