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
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Organization $organizationConfiguration,
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Social $socialConfiguration
    ) {}

    public function getOrganizationData(): array
    {
        $store = $this->storeManager->getStore();
        $logoUrl = $this->organizationConfiguration->getLogo() ?? $this->logo->getLogoSrc();
        $name = $this->organizationConfiguration->getName() ?? $store->getName();
        $legalName = $this->organizationConfiguration->getLegalName();

        $organizationData = [
            "@type" => "Organization",
            "@id" => $this->configuration->getEntityIdBase() . '#organization',
            "name" => $name,
            "url" => $store->getBaseUrl(),
            "logo" => [
                "@type" => "ImageObject",
                "url" => $logoUrl
            ],
        ];

        if (!empty($legalName)) {
            $organizationData['legalName'] = $legalName;
        }

        $organizationData = $this->addDescription($organizationData);
        $organizationData = $this->addAlternateName($organizationData);
        $organizationData = $this->addFoundingDate($organizationData);
        $organizationData = $this->addDefaultContactData($organizationData);
        $organizationData = $this->addAddressData($organizationData);
        $organizationData = $this->addContactData($organizationData);
        $organizationData = $this->addReturnPolicy($organizationData);
        $organizationData = $this->addSameAs($organizationData);

        return $organizationData;
    }

    public function addDescription(array $organizationData): array
    {
        $description = $this->organizationConfiguration->getDescription();

        if (!$description) {
            return $organizationData;
        }

        $organizationData['description'] = $description;

        return $organizationData;
    }

    public function addAlternateName(array $organizationData): array
    {
        $alternateName = $this->organizationConfiguration->getAlternateName();

        if (!$alternateName) {
            return $organizationData;
        }

        $names = $this->splitLinesToArray($alternateName);

        if (empty($names)) {
            return $organizationData;
        }

        $organizationData['alternateName'] = $names;

        return $organizationData;
    }

    public function addFoundingDate(array $organizationData): array
    {
        $foundingDate = $this->organizationConfiguration->getFoundingDate();

        if (!$foundingDate) {
            return $organizationData;
        }

        $organizationData['foundingDate'] = $foundingDate;

        return $organizationData;
    }

    protected function splitLinesToArray(string $value): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $value);
        $result = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $result[] = $line;
        }

        return $result;
    }

    public function addSameAs(array $organizationData): array
    {
        $profiles = array_filter($this->socialConfiguration->getSocialProfiles());

        if (empty($profiles)) {
            return $organizationData;
        }

        $organizationData['sameAs'] = array_values($profiles);

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
                    'contactType' => $this->contactFieldsMapping[$key]
                ];
            }

            if (str_contains($key, '_email')) {
                $contact[$this->contactFieldsMapping[$key]]['email'] = $value;
            }
            if (str_contains($key, '_telephone')) {
                $contact[$this->contactFieldsMapping[$key]]['telephone'] = $value;
            }

            if (!empty($contactData['area_served'])) {
                $contact[$this->contactFieldsMapping[$key]]['areaServed'] = explode(',', $contactData['area_served']);
            }

            if (!empty($contactData['available_language'])) {
                $contact[$this->contactFieldsMapping[$key]]['availableLanguage'] = explode(',', $contactData['available_language']);
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
        $storeId = (int)$store->getId();

        if (!$this->organizationConfiguration->isReturnPolicyEnabled($storeId)) {
            return $organizationData;
        }

        $country = $this->configuration->getCountryByWebsite($store->getWebsite());
        $returnPolicyCategory = $this->organizationConfiguration->getReturnPolicyCategory($storeId);
        $returnPolicyLink = $this->organizationConfiguration->getReturnPolicyLink($storeId);
        $returnMethod = $this->organizationConfiguration->getReturnMethod($storeId);
        $returnFees = $this->organizationConfiguration->getReturnFees($storeId);
        $refundType = $this->organizationConfiguration->getRefundType($storeId);

        $returnPolicyData = ['@type' => 'MerchantReturnPolicy'];

        if ($returnPolicyLink) {
            $returnPolicyData['merchantReturnLink'] = $returnPolicyLink;
        }

        $returnPolicyData['applicableCountry'] = $country;
        $returnPolicyData['returnPolicyCategory'] = sprintf('https://schema.org/%s', $returnPolicyCategory);

        if ($returnPolicyCategory === \MageSuite\GoogleStructuredData\Model\Config\Source\ReturnPolicyCategory::FINITE_RETURN_WINDOW) {
            $returnPolicyData['merchantReturnDays'] = $this->organizationConfiguration->getReturnDays($storeId);
        }

        if ($returnMethod) {
            $returnPolicyData['returnMethod'] = sprintf('https://schema.org/%s', $returnMethod);
        }

        if ($returnFees) {
            $returnPolicyData['returnFees'] = sprintf('https://schema.org/%s', $returnFees);
        }

        if ($refundType) {
            $returnPolicyData['refundType'] = sprintf('https://schema.org/%s', $refundType);
        }

        $organizationData['hasMerchantReturnPolicy'] = $returnPolicyData;

        return $organizationData;
    }

    public function addDefaultContactData(array $organizationData): array
    {
        $storeId = (int)$this->storeManager->getStore()->getId();

        $telephone = $this->organizationConfiguration->getTelephone($storeId);

        if ($telephone) {
            $organizationData['telephone'] = $telephone;
        }

        $email = $this->organizationConfiguration->getEmail($storeId);

        if ($email) {
            $organizationData['email'] = $email;
        }

        return $organizationData;
    }
}
