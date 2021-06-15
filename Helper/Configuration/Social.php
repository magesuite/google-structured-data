<?php

namespace MageSuite\GoogleStructuredData\Helper\Configuration;

class Social extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_SOCIAL = 'structured_data/social';

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $config;

    public function isEnabled()
    {
        return (bool) $this->getConfig()->getEnabled();
    }

    public function getSocialProfiles()
    {
        $config = $this->getConfig();
        $config->unsetData('enabled');

        return $config;
    }

    protected function getConfig()
    {
        if ($this->config === null) {
            $config = $this->scopeConfig->getValue(self::XML_PATH_SOCIAL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $this->config = new \Magento\Framework\DataObject($config);
        }

        return $this->config;
    }
}
