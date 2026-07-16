<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data;

class Website
{
    public function __construct(
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
        protected \MageSuite\GoogleStructuredData\Helper\Configuration $configuration,
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Organization $organizationConfiguration
    ) {}

    public function getWebsiteData(): array
    {
        $store = $this->storeManager->getStore();
        $idBase = $this->configuration->getEntityIdBase();

        return [
            "@type" => "WebSite",
            "@id" => $idBase . '#website',
            "url" => $store->getBaseUrl(),
            "name" => $this->resolveName($store),
            "inLanguage" => $this->configuration->getLocale(),
            "publisher" => [
                "@id" => $idBase . '#organization'
            ]
        ];
    }

    protected function resolveName(\Magento\Store\Api\Data\StoreInterface $store): string
    {
        $configuredName = $this->configuration->getWebsiteName();

        if ($configuredName) {
            return $configuredName;
        }

        $websiteName = (string)$store->getWebsite()->getName();

        if ($websiteName !== '') {
            return $websiteName;
        }

        return (string)$this->organizationConfiguration->getName();
    }
}
