<?php
namespace MageSuite\GoogleStructuredData\Service;

class JsonLdCreator
{
    /**
     * @var \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer
     */
    private $structuredDataContainer;
    /**
     * @var \MageSuite\GoogleStructuredData\Provider\Data\SearchBox
     */
    private $searchBoxDataProvider;
    /**
     * @var \MageSuite\GoogleStructuredData\Provider\Data\Organization
     */
    private $organizationDataProvider;


    public function __construct(
        \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        \MageSuite\GoogleStructuredData\Provider\Data\SearchBox $searchBoxDataProvider,
        \MageSuite\GoogleStructuredData\Provider\Data\Organization $organizationDataProvider
    )
    {
        $this->structuredDataContainer = $structuredDataContainer;
        $this->searchBoxDataProvider = $searchBoxDataProvider;
        $this->organizationDataProvider = $organizationDataProvider;
    }


    public function getRenderedJsonLd()
    {
        $this->addOrganizationStructuredData();
        $this->addSearchBoxStructuredData();

        $structuredData = $this->structuredDataContainer->structuredData();

        $jsonLd = '';
        foreach ($structuredData as $data) {
            $jsonLd .= sprintf('<script type="application/ld+json">%s</script>', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        return $jsonLd;
    }

    public function addSearchBoxStructuredData()
    {
        $searchBoxData = $this->searchBoxDataProvider->getSearchBoxData();

        $structuredDataContainer = $this->structuredDataContainer;

        $structuredDataContainer->add($searchBoxData, $structuredDataContainer::SEARCH);
    }

    public function addOrganizationStructuredData()
    {
        $organizationData = $this->organizationDataProvider->getOrganizationData();

        $structuredDataContainer = $this->structuredDataContainer;

        $structuredDataContainer->add($organizationData, $structuredDataContainer::ORGANIZATION);
    }
}