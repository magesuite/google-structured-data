<?php

namespace MageSuite\GoogleStructuredData\Provider\Data;

class Organization
{
    /**
     * @var \Magento\Theme\Block\Html\Header\Logo
     */
    protected $logo;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \MageSuite\GoogleStructuredData\Helper\Configuration\Organization
     */
    protected $configuration;

    protected $addressFieldsMapping = [
        'postal' => 'postalCode',
        'city' => 'addressLocality',
        'street' => 'streetAddress',
        'region' => 'addressRegion',
        'country' => 'addressCountry'
    ];

    protected $contactFieldsMapping = [
        'sales_telephone' => 'sales',
        'sales_email' => 'sales',
        'technical_telephone' => 'technical support',
        'technical_email' => 'technical support',
        'customer_service_telephone' => 'customer service',
        'customer_service_email' => 'customer service'
    ];

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Theme\Block\Html\Header\Logo $logo,
        \MageSuite\GoogleStructuredData\Helper\Configuration\Organization $configuration
    ) {
        $this->storeManager = $storeManager;
        $this->logo = $logo;
        $this->configuration = $configuration;
    }

    public function getOrganizationData()
    {
        $store = $this->storeManager->getStore();
        $logoUrl = empty($this->configuration->getLogo()) ? $this->logo->getLogoSrc() : $this->configuration->getLogo();
        $name = empty($this->configuration->getName()) ? $store->getName() : $this->configuration->getName();

        $organizationData = [
            "@context" => "http://schema.org",
            "@type" => "Organization",
            "name" => $name,
            "url" => $store->getBaseUrl(),
            "logo" => $logoUrl
        ];

        $address = ['@type' => 'PostalAddress'];
        $addressData = $this->configuration->getAddressData();
        foreach ($addressData as $key => $value) {
            if (!isset($this->addressFieldsMapping, $key)) {
                continue;
            }
            $address[$this->addressFieldsMapping[$key]] = $value;
        }
        if (count($address) > 1) {
            $organizationData['address'] = $address;
        }

        $contact = [];
        $contactData = $this->configuration->getContactData();
        foreach ($contactData as $key => $value) {
            if (!isset($this->contactFieldsMapping[$key])) {
                continue;
            }

            if (!isset($contact[$this->contactFieldsMapping[$key]])) {
                $contact[$this->contactFieldsMapping[$key]] = [
                    '@type' => 'ContactPoint',
                    'contactType' => 'sales'
                ];
            }

            if (strpos($key, '_email') !== false) {
                $contact[$this->contactFieldsMapping[$key]]['email'] = $value;
            }
            if (strpos($key, '_telephone') !== false) {
                $contact[$this->contactFieldsMapping[$key]]['telephone'] = $value;
            }

        }

        foreach ($contact as $item) {
            $organizationData['contactPoint'][] = array_filter($item);
        }

        return $organizationData;
    }
}
