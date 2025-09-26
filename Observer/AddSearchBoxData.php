<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Observer;

class AddSearchBoxData implements \Magento\Framework\Event\ObserverInterface
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Helper\Configuration $configuration,
        protected \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        protected \MageSuite\GoogleStructuredData\Provider\Data\SearchBox $searchBoxDataProvider
    ) {}

    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        if (!$this->configuration->isSearchBoxEnabled()) {
            return;
        }

        $searchBoxData = $this->searchBoxDataProvider->getSearchBoxData();

        $this->structuredDataContainer->add($searchBoxData, 'search');
    }
}
