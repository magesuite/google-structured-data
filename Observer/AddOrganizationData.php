<?php
namespace MageSuite\GoogleStructuredData\Observer;

class AddOrganizationData implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer
     */
    protected $structuredDataContainer;

    /**
     * @var \MageSuite\GoogleStructuredData\Provider\Data\Organization
     */
    protected $organizationDataProvider;

    /**
     * @var \MageSuite\GoogleStructuredData\Helper\Organization
     */
    protected $configuration;

    public function __construct(
        \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        \MageSuite\GoogleStructuredData\Provider\Data\Organization $organizationDataProvider,
        \MageSuite\GoogleStructuredData\Helper\Organization $configuration
    )
    {
        $this->structuredDataContainer = $structuredDataContainer;
        $this->organizationDataProvider = $organizationDataProvider;
        $this->configuration = $configuration;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->configuration->isEnabled()) {
            return;
        }

        $organizationData = $this->organizationDataProvider->getOrganizationData();

        $this->structuredDataContainer->add($organizationData, 'organization');
    }
}
