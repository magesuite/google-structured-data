<?php

namespace MageSuite\GoogleStructuredData\Observer;

class AddSearchBoxData implements \Magento\Framework\Event\ObserverInterface
{
    protected \MageSuite\GoogleStructuredData\Helper\Configuration $configuration;
    protected \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer;
    protected \MageSuite\GoogleStructuredData\Provider\Data\SearchBox $searchBoxDataProvider;

    public function __construct(
        \MageSuite\GoogleStructuredData\Helper\Configuration $configuration,
        \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        \MageSuite\GoogleStructuredData\Provider\Data\SearchBox $searchBoxDataProvider
    ) {
        $this->configuration = $configuration;
        $this->structuredDataContainer = $structuredDataContainer;
        $this->searchBoxDataProvider = $searchBoxDataProvider;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->configuration->isSearchBoxEnabled()) {
            return;
        }

        $searchBoxData = $this->searchBoxDataProvider->getSearchBoxData();

        $this->structuredDataContainer->add($searchBoxData, 'search');
    }
}
