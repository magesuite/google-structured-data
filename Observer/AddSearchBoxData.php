<?php
namespace MageSuite\GoogleStructuredData\Observer;

class AddSearchBoxData implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer
     */
    private $structuredDataContainer;
    /**
     * @var \MageSuite\GoogleStructuredData\Provider\Data\SearchBox
     */
    private $searchBoxDataProvider;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        \MageSuite\GoogleStructuredData\Provider\Data\SearchBox $searchBoxDataProvider,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->structuredDataContainer = $structuredDataContainer;
        $this->searchBoxDataProvider = $searchBoxDataProvider;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if(!$this->scopeConfig->getValue('structured_data/search_box/enabled')){
            return;
        }
        $searchBoxData = $this->searchBoxDataProvider->getSearchBoxData();

        $this->structuredDataContainer->add($searchBoxData, 'search');

    }
}