<?php
namespace MageSuite\GoogleStructuredData\Observer;

class AddAdditionalDataToRenderedPage implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageSuite\GoogleStructuredData\Provider\StructuredDataProvider
     */
    private $structuredDataProvider;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;


    public function __construct(
        \MageSuite\GoogleStructuredData\Provider\StructuredDataProvider $structuredDataProvider,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\UrlInterface $urlBuilder
    )
    {
        $this->structuredDataProvider = $structuredDataProvider;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $response = $observer->getResponse();

        $html = $response->getBody();

        if ($html == '') {
            return;
        }

        $this->addOrganizationStructuredData();
        $this->addSearchBoxStructuredData();

        $structuredData = $this->structuredDataProvider->structuredData();

        foreach ($structuredData as $data) {
            $additional = '<script type="application/ld+json">' . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</script></body>';

            $html = str_replace('</body>', $additional, $html);
        }

        $response->setBody($html);
    }

    public function addSearchBoxStructuredData()
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

        $structuredDataProvider = $this->structuredDataProvider;

        $structuredDataProvider->add($searchBoxData, $structuredDataProvider::SEARCH);
    }

    public function addOrganizationStructuredData()
    {
        $folderName = \Magento\Config\Model\Config\Backend\Image\Logo::UPLOAD_DIR;

        $storeLogoPath = $this->scopeConfig->getValue(
            'design/header/logo_src',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $path = $folderName . '/' . $storeLogoPath;
        $logoUrl = $this->urlBuilder
                ->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $path;

        $store = $this->storeManager->getStore();
        $baseUrl = $store->getBaseUrl();

        $organizationData = [
            "@context" => "http://schema.org",
            "@type" => "Organization",
            "name" => $store->getName(),
            "url" => $baseUrl,
            "logo" => $logoUrl
        ];

        $structuredDataProvider = $this->structuredDataProvider;

        $structuredDataProvider->add($organizationData, $structuredDataProvider::ORGANIZATION);
    }
}