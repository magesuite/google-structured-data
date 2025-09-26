<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Observer;

class AddOrganizationData implements \Magento\Framework\Event\ObserverInterface
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        protected \MageSuite\GoogleStructuredData\Provider\Data\Organization $organizationDataProvider,
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Organization $configuration
    ) {}

    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        if (!$this->configuration->isEnabled()) {
            return;
        }

        $organizationData = $this->organizationDataProvider->getOrganizationData();

        $this->structuredDataContainer->add($organizationData, 'organization');
    }
}
