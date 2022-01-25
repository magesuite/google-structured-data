<?php

namespace MageSuite\GoogleStructuredData\Observer;

class AddSocialData implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var  \MageSuite\GoogleStructuredData\Helper\Configuration\Social
     */
    protected $configuration;

    /**
     * @var \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer
     */
    protected $structuredDataContainer;

    /**
     * @var \MageSuite\GoogleStructuredData\Provider\Data\Social
     */
    protected $socialDataProvider;

    public function __construct(
        \MageSuite\GoogleStructuredData\Helper\Configuration\Social $configuration,
        \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        \MageSuite\GoogleStructuredData\Provider\Data\Social $socialDataProvider
    ) {
        $this->configuration = $configuration;
        $this->structuredDataContainer = $structuredDataContainer;
        $this->socialDataProvider = $socialDataProvider;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->configuration->isEnabled()) {
            return;
        }

        $socialData = $this->socialDataProvider->getSocialData();

        $this->structuredDataContainer->add($socialData, 'social');
    }
}
