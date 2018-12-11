<?php
namespace MageSuite\GoogleStructuredData\Service;

class JsonLdCreator
{
    /**
     * @var \MageSuite\GoogleStructuredData\Provider\StructuredDataProvider
     */
    private $structuredDataProvider;
    /**
     * @var \MageSuite\GoogleStructuredData\Provider\Data\SearchBox
     */
    private $searchBoxDataProvider;
    /**
     * @var \MageSuite\GoogleStructuredData\Provider\Data\Organization
     */
    private $organizationDataProvider;


    public function __construct(
        \MageSuite\GoogleStructuredData\Provider\StructuredDataProvider $structuredDataProvider,
        \MageSuite\GoogleStructuredData\Provider\Data\SearchBox $searchBoxDataProvider,
        \MageSuite\GoogleStructuredData\Provider\Data\Organization $organizationDataProvider
    )
    {
        $this->structuredDataProvider = $structuredDataProvider;
        $this->searchBoxDataProvider = $searchBoxDataProvider;
        $this->organizationDataProvider = $organizationDataProvider;
    }


    public function getRenderedJsonLd()
    {
        $this->addOrganizationStructuredData();
        $this->addSearchBoxStructuredData();

        $structuredData = $this->structuredDataProvider->structuredData();

        $jsonLd = '';
        foreach ($structuredData as $data) {
            $jsonLd .= '<script type="application/ld+json">' . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</script>';
        }

        return $jsonLd;
    }

    public function addSearchBoxStructuredData()
    {
        $searchBoxData = $this->searchBoxDataProvider->getSearchBoxData();

        $structuredDataProvider = $this->structuredDataProvider;

        $structuredDataProvider->add($searchBoxData, $structuredDataProvider::SEARCH);
    }

    public function addOrganizationStructuredData()
    {
        $organizationData = $this->organizationDataProvider->getOrganizationData();

        $structuredDataProvider = $this->structuredDataProvider;

        $structuredDataProvider->add($organizationData, $structuredDataProvider::ORGANIZATION);
    }
}