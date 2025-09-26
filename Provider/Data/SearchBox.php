<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data;

class SearchBox
{
    public function __construct(
        protected \Magento\Framework\UrlInterface $urlBuilder,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {}

    public function getSearchBoxData(): array
    {
        $store = $this->storeManager->getStore();
        $baseUrl = $store->getBaseUrl();

        $searchUrl = $this->urlBuilder->getUrl('catalogsearch/result/?q={search_term_string}');

        return [
            "@context" => "http://schema.org",
            "@type" => "WebSite",
            "url" => $baseUrl,
            "potentialAction" => [
                "@type" => "SearchAction",
                "target" => $searchUrl,
                "query-input" => "required name=search_term_string"
            ]
        ];
    }
}
