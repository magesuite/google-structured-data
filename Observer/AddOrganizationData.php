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
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;


    public function __construct(
        \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        \MageSuite\GoogleStructuredData\Provider\Data\Organization $organizationDataProvider,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->structuredDataContainer = $structuredDataContainer;
        $this->organizationDataProvider = $organizationDataProvider;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if(!$this->scopeConfig->getValue('structured_data/organization/enabled')){
            return;
        }

        $organizationData = $this->organizationDataProvider->getOrganizationData();

        $this->structuredDataContainer->add($organizationData, 'organization');
    }
}