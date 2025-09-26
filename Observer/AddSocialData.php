<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Observer;

class AddSocialData implements \Magento\Framework\Event\ObserverInterface
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Social $configuration,
        protected \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        protected \MageSuite\GoogleStructuredData\Provider\Data\Social $socialDataProvider
    ) {}

    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        if (!$this->configuration->isEnabled()) {
            return;
        }

        $socialData = $this->socialDataProvider->getSocialData();

        $this->structuredDataContainer->add($socialData, 'social');
    }
}
