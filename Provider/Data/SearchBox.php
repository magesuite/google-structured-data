<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data;

class SearchBox
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Organization $organizationConfiguration,
        protected \Magento\Framework\UrlInterface $urlBuilder,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {}

    public function getSearchBoxData(): array
    {
        $store = $this->storeManager->getStore();
        $baseUrl = $store->getBaseUrl();
        $name = $this->organizationConfiguration->getName() ?? $store->getName();
        $searchUrl = rtrim($this->urlBuilder->getUrl('catalogsearch/result/?q={search_term_string}'), '/');

        return [
            "@context" => "https://schema.org",
            "@type" => "WebSite",
            "url" => $baseUrl,
            "name" => $name,
            "potentialAction" => [
                "@type" => "SearchAction",
                "target" => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $searchUrl
                ],
                "query-input" => "required name=search_term_string"
            ]
        ];
    }
}
