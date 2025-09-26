<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Helper\Configuration;

class Social
{
    public const XML_PATH_SOCIAL_IS_ENABLED = 'structured_data/social/is_enabled';
    public const XML_PATH_SOCIAL_PROFILES = 'structured_data/social/profiles';

    public function __construct(
        protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {}

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SOCIAL_IS_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getSocialProfiles(): array
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SOCIAL_PROFILES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?? [];
    }
}
