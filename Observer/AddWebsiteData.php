<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Observer;

class AddWebsiteData implements \Magento\Framework\Event\ObserverInterface
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Helper\Configuration $configuration,
        protected \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        protected \MageSuite\GoogleStructuredData\Provider\Data\Website $websiteDataProvider
    ) {}

    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        if (!$this->configuration->isWebsiteEnabled()) {
            return;
        }

        $websiteData = $this->websiteDataProvider->getWebsiteData();

        $this->structuredDataContainer->add($websiteData, 'website');
    }
}
