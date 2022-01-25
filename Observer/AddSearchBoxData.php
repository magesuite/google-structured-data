<?php

namespace MageSuite\GoogleStructuredData\Observer;

class AddSearchBoxData implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageSuite\GoogleStructuredData\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer
     */
    protected $structuredDataContainer;

    /**
     * @var \MageSuite\GoogleStructuredData\Provider\Data\SearchBox
     */
    protected $searchBoxDataProvider;

    public function __construct(
        \MageSuite\GoogleStructuredData\Helper\Configuration $configuration,
        \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        \MageSuite\GoogleStructuredData\Provider\Data\SearchBox $searchBoxDataProvider
    ) {
        $this->configuration = $configuration;
        $this->structuredDataContainer = $structuredDataContainer;
        $this->searchBoxDataProvider = $searchBoxDataProvider;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->configuration->isSearchBoxEnabled()) {
            return;
        }

        $searchBoxData = $this->searchBoxDataProvider->getSearchBoxData();

        $this->structuredDataContainer->add($searchBoxData, 'search');
    }
}
