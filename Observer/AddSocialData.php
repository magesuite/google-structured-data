<?php
namespace MageSuite\GoogleStructuredData\Observer;

class AddSocialData implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer
     */
    protected $structuredDataContainer;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \MageSuite\GoogleStructuredData\Provider\Data\Social
     */
    protected $socialDataProvider;


    public function __construct(
        \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        \MageSuite\GoogleStructuredData\Provider\Data\Social $socialDataProvider,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->structuredDataContainer = $structuredDataContainer;
        $this->scopeConfig = $scopeConfig;
        $this->socialDataProvider = $socialDataProvider;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if(!$this->scopeConfig->getValue('structured_data/social/enabled')){
            return;
        }

        $socialData = $this->socialDataProvider->getSocialData();

        $this->structuredDataContainer->add($socialData, 'social');
    }
}