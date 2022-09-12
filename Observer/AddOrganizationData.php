<?php

namespace MageSuite\GoogleStructuredData\Observer;

class AddOrganizationData implements \Magento\Framework\Event\ObserverInterface
{
    protected \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer;

    protected \MageSuite\GoogleStructuredData\Provider\Data\Organization $organizationDataProvider;

    protected \MageSuite\GoogleStructuredData\Helper\Configuration\Organization $configuration;

    public function __construct(
        \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        \MageSuite\GoogleStructuredData\Provider\Data\Organization $organizationDataProvider,
        \MageSuite\GoogleStructuredData\Helper\Configuration\Organization $configuration
    ) {
        $this->structuredDataContainer = $structuredDataContainer;
        $this->organizationDataProvider = $organizationDataProvider;
        $this->configuration = $configuration;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->configuration->isEnabled()) {
            return;
        }

        $organizationData = $this->organizationDataProvider->getOrganizationData();

        $this->structuredDataContainer->add($organizationData, 'organization');
    }
}
