<?php

namespace MageSuite\GoogleStructuredData\Provider\Data;

class SearchBox
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {

        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
    }

    public function getSearchBoxData()
    {
        $store = $this->storeManager->getStore();
        $baseUrl = $store->getBaseUrl();

        $searchUrl = $this->urlBuilder->getUrl('catalogsearch/result/?q={search_term_string}');

        $searchBoxData = [
            "@context" => "http://schema.org",
            "@type" => "WebSite",
            "url" => $baseUrl,
            "potentialAction" => [
                "@type" => "SearchAction",
                "target" => $searchUrl,
                "query-input" => "required name=search_term_string"
            ]
        ];

        return $searchBoxData;
    }
}
