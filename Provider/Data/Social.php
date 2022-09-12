<?php

namespace MageSuite\GoogleStructuredData\Provider\Data;

class Social
{
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;

    protected \MageSuite\GoogleStructuredData\Helper\Configuration\Social $configuration;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageSuite\GoogleStructuredData\Helper\Configuration\Social $configuration
    ) {
        $this->storeManager = $storeManager;
        $this->configuration = $configuration;
    }

    public function getSocialData()
    {
        $store = $this->storeManager->getStore();
        $baseUrl = $store->getBaseUrl();
        $socialData = [
            "@context" => "http://schema.org",
            "@type" => "Person",
            "name" => $store->getName(),
            "url" => $baseUrl
        ];

        $socialProfiles = $this->configuration->getSocialProfiles();
        foreach ($socialProfiles as $socialProfile) {
            if (!$socialProfile) {
                continue;
            }

            $socialData['sameAs'][] = $socialProfile;
        }

        return $socialData;
    }
}
