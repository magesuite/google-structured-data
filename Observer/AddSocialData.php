<?php

namespace MageSuite\GoogleStructuredData\Observer;

class AddSocialData implements \Magento\Framework\Event\ObserverInterface
{
    protected \MageSuite\GoogleStructuredData\Helper\Configuration\Social $configuration;

    protected \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer;

    protected \MageSuite\GoogleStructuredData\Provider\Data\Social $socialDataProvider;

    public function __construct(
        \MageSuite\GoogleStructuredData\Helper\Configuration\Social $configuration,
        \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        \MageSuite\GoogleStructuredData\Provider\Data\Social $socialDataProvider
    ) {
        $this->configuration = $configuration;
        $this->structuredDataContainer = $structuredDataContainer;
        $this->socialDataProvider = $socialDataProvider;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->configuration->isEnabled()) {
            return;
        }

        $socialData = $this->socialDataProvider->getSocialData();

        $this->structuredDataContainer->add($socialData, 'social');
    }
}
