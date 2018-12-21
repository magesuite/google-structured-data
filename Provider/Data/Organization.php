<?php
namespace MageSuite\GoogleStructuredData\Provider\Data;

class Organization
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\UrlInterface $urlBuilder
    )
    {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
    }

    public function getOrganizationData()
    {
        $config = $this->getConfiguration();

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

        $logoUrl = (isset($config['logo']) && $config['logo']) ? $config['logo'] : $logoUrl;
        $name = (isset($config['name']) && $config['name']) ? $config['name'] : $store->getName();

        $organizationData = [
            "@context" => "http://schema.org",
            "@type" => "Organization",
            "name" => $name,
            "url" => $baseUrl,
            "logo" => $logoUrl
        ];

        $contactData = [];
        if(isset($config['sales']) && $config['sales']){
            $contactData['sales'] = [
                '@type' => 'ContactPoint',
                'telephone' => $config['sales'],
                'contactType' => 'sales'
            ];
        }

        if(isset($config['technical']) && $config['technical']){
            $contactData['technical'] = [
                '@type' => 'ContactPoint',
                'telephone' => $config['technical'],
                'contactType' => 'technical support'
            ];
        }

        if(isset($config['customer_service']) && $config['customer_service']) {
            $contactData['customer_service'] = [
                '@type' => 'ContactPoint',
                'telephone' => $config['customer_service'],
                'contactType' => 'customer service'
            ];
        }

        foreach ($contactData as $contact) {
            $organizationData['contactPoint'][] = $contact;
        }

        $address = [
            '@type' => 'PostalAddress'
        ];

        if(isset($config['postal']) && $config['postal']){
            $address['postalCode'] = $config['postal'];
        }

        if(isset($config['region']) && $config['region']){
            $address['addressRegion'] = $config['region'];
        }

        if(isset($config['city']) && $config['city']){
            $address['addressLocality'] = $config['city'];
        }

        if(isset($config['country']) && $config['country']){
            $address['addressCountry'] = [
                '@type' => 'Country',
                'name' => $config['country']
            ];
        }

        if(isset($config['postal']) && $config['postal']){
            $address['postalCode'] = $config['postal'];
        }

        if(count($address) > 1) {
            $organizationData['address'] = $address;
        }

        return $organizationData;
    }

    public function getConfiguration()
    {
        return $this->scopeConfig->getValue('structured_data/organization');
    }
}