<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data;

class Social
{
    public function __construct(
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Social $configuration,
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Organization $organizationConfiguration
    ) {}

    public function getSocialData(): array
    {
        $store = $this->storeManager->getStore();
        $name = $this->organizationConfiguration->getName() ?? $store->getName();
        $baseUrl = $store->getBaseUrl();
        $socialData = [
            "@context" => "https://schema.org",
            "@type" => "Person",
            "name" => $name,
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
