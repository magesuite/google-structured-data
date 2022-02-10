<?php

namespace MageSuite\GoogleStructuredData\Helper\Configuration;

class Social extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_SOCIAL_IS_ENABLED = 'structured_data/social/is_enabled';
    const XML_PATH_SOCIAL_PROFILES = 'structured_data/social/profiles';

    public function isEnabled()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_SOCIAL_IS_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getSocialProfiles()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SOCIAL_PROFILES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?? [];
    }
}
