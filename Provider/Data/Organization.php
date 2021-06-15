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
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \MageSuite\GoogleStructuredData\Helper\Configuration\Organization
     */
    protected $configuration;

    public function __construct(
        \Magento\Theme\Block\Html\Header\Logo $logo,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \MageSuite\GoogleStructuredData\Helper\Configuration\Organization $configuration
    ) {
        $this->logo = $logo;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
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
        $contactData = [];

        if (!empty($this->configuration->getSales())) {
            $contactData['sales'] = [
                '@type' => 'ContactPoint',
                'telephone' => $this->configuration->getSales(),
                'contactType' => 'sales'
            ];
        }

        if (!empty($this->configuration->getTechnical())) {
            $contactData['technical'] = [
                '@type' => 'ContactPoint',
                'telephone' => $this->configuration->getTechnical(),
                'contactType' => 'technical support'
            ];
        }

        if (!empty($this->configuration->getCustomerService())) {
            $contactData['customer_service'] = [
                '@type' => 'ContactPoint',
                'telephone' => $this->configuration->getCustomerService(),
                'contactType' => 'customer service'
            ];
        }

        foreach ($contactData as $contact) {
            $organizationData['contactPoint'][] = $contact;
        }

        $address = ['@type' => 'PostalAddress'];

        if (!empty($this->configuration->getPostal())) {
            $address['postalCode'] = $this->configuration->getPostal();
        }

        if (!empty($this->configuration->getRegion())) {
            $address['addressRegion'] = $this->configuration->getRegion();
        }

        if (!empty($this->configuration->getCity())) {
            $address['addressLocality'] = $this->configuration->getCity();
        }

        if (!empty($this->configuration->getCountry())) {
            $address['addressCountry'] = [
                '@type' => 'Country',
                'name' => $this->configuration->getCountry()
            ];
        }

        if (!empty($this->configuration->getPostal())) {
            $address['postalCode'] = $this->configuration->getPostal();
        }

        if (count($address) > 1) {
            $organizationData['address'] = $address;
        }

        return $organizationData;
    }
}
