<?php
namespace MageSuite\GoogleStructuredData\Provider\Data;

class SearchBox
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ){

        $this->storeManager = $storeManager;
    }

    public function getSearchBoxData()
    {
        $store = $this->storeManager->getStore();
        $baseUrl = $store->getBaseUrl();

        $searchUrl = $baseUrl . 'catalogsearch/result/?q={search_term_string}';

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