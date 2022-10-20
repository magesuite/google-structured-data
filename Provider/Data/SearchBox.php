<?php

namespace MageSuite\GoogleStructuredData\Provider\Data;

class SearchBox
{
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;

    protected \Magento\Framework\UrlInterface $urlBuilder;

    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {

        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
    }

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
