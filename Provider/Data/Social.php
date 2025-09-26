<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data;

class Social
{
    public function __construct(
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Social $configuration
    ) {}

    public function getSocialData(): array
    {
        $store = $this->storeManager->getStore();
        $baseUrl = $store->getBaseUrl();
        $socialData = [
            "@context" => "https://schema.org",
            "@type" => "Person",
            "name" => $store->getName(),
            "url" => $baseUrl,
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
