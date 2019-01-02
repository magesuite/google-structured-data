<?php
namespace MageSuite\GoogleStructuredData\Provider\Data;

class Social
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    public function getSocialData()
    {
        $config = $this->getConfiguration();

        unset($config['enabled']);

        $store = $this->storeManager->getStore();
        $baseUrl = $store->getBaseUrl();


        $socialData = [
            "@context" => "http://schema.org",
            "@type" => "Person",
            "name" => $store->getName(),
            "url" => $baseUrl
        ];

        foreach ($config as $socialProfile) {
            if(!$socialProfile){
                continue;
            }
            $socialData['sameAs'][] = $socialProfile;
        }


        return $socialData;
    }

    public function getConfiguration()
    {
        return $this->scopeConfig->getValue('structured_data/social');
    }
}