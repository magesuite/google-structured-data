<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data;

class Organization
{
    protected array $addressFieldsMapping = [
        'postal' => 'postalCode',
        'city' => 'addressLocality',
        'street' => 'streetAddress',
        'region' => 'addressRegion',
        'country' => 'addressCountry'
    ];

    protected array $contactFieldsMapping = [
        'sales_telephone' => 'sales',
        'sales_email' => 'sales',
        'technical_telephone' => 'technical support',
        'technical_email' => 'technical support',
        'customer_service_telephone' => 'customer service',
        'customer_service_email' => 'customer service'
    ];

    public function __construct(
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
        protected \Magento\Theme\Block\Html\Header\Logo $logo,
        protected \MageSuite\GoogleStructuredData\Helper\Configuration $configuration,
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Organization $organizationConfiguration
    ) {}

    public function getOrganizationData(): array
    {
        $store = $this->storeManager->getStore();
        $logoUrl = $this->organizationConfiguration->getLogo() ?? $this->logo->getLogoSrc();
        $name = $this->organizationConfiguration->getName() ?? $store->getName();

        $organizationData = [
            "@context" => "http://schema.org",
            "@type" => "Organization",
            "name" => $name,
            "url" => $store->getBaseUrl(),
            "logo" => $logoUrl
        ];

        $organizationData = $this->addAddressData($organizationData);
        $organizationData = $this->addContactData($organizationData);
        $organizationData = $this->addReturnPolicy($organizationData);

        return $organizationData;
    }

    public function addAddressData(array $organizationData): array
    {
        $address = ['@type' => 'PostalAddress'];
        $addressData = $this->organizationConfiguration->getAddressData();
        foreach ($addressData as $key => $value) {
            if (!isset($this->addressFieldsMapping[$key])) {
                continue;
            }
            $address[$this->addressFieldsMapping[$key]] = $value;
        }
        if (count($address) > 1) {
            $organizationData['address'] = $address;
        }

        return $organizationData;
    }

    public function addContactData(array $organizationData): array
    {
        $contact = [];
        $contactData = $this->organizationConfiguration->getContactData();

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

            if (str_contains($key, '_email')) {
                $contact[$this->contactFieldsMapping[$key]]['email'] = $value;
            }
            if (str_contains($key, '_telephone')) {
                $contact[$this->contactFieldsMapping[$key]]['telephone'] = $value;
            }
        }

        foreach ($contact as $item) {
            $organizationData['contactPoint'][] = array_filter($item);
        }

        return $organizationData;
    }

    public function addReturnPolicy(array $organizationData): array
    {
        $store = $this->storeManager->getStore();

        if (!$this->organizationConfiguration->isReturnPolicyEnabled((int)$store->getId())) {
            return $organizationData;
        }

        $country = $this->configuration->getCountryByWebsite($store->getWebsite());
        $returnPolicyCategory = $this->organizationConfiguration->getReturnPolicyCategory((int)$store->getId());
        $returnPolicyLink = $this->organizationConfiguration->getReturnPolicyLink((int)$store->getId());

        $returnPolicyData = ['@type' => 'MerchantReturnPolicy'];

        if ($returnPolicyLink) {
            $returnPolicyData['merchantReturnLink'] = $returnPolicyLink;

            $organizationData['hasMerchantReturnPolicy'] = $returnPolicyData;

            return $organizationData;
        }

        $returnPolicyData['applicableCountry'] = $country;
        $returnPolicyData['returnPolicyCategory'] = sprintf('https://schema.org/%s', $returnPolicyCategory);

        if ($returnPolicyCategory === \MageSuite\GoogleStructuredData\Model\Config\Source\ReturnPolicyCategory::FINITE_RETURN_WINDOW) {
            $returnPolicyData['merchantReturnDays'] = $this->organizationConfiguration->getReturnDays((int)$store->getId());
        }

        $organizationData['hasMerchantReturnPolicy'] = $returnPolicyData;

        return $organizationData;
    }
}
