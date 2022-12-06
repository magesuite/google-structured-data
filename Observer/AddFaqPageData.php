<?php
declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Observer;

class AddFaqPageData implements \Magento\Framework\Event\ObserverInterface
{
    protected \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer;

    protected \MageSuite\GoogleStructuredData\Provider\Data\FaqPage $faqPageDataProvider;

    protected \MageSuite\GoogleStructuredData\Helper\Configuration\FaqPage $configuration;

    public function __construct(
        \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        \MageSuite\GoogleStructuredData\Provider\Data\FaqPage $faqPageDataProvider,
        \MageSuite\GoogleStructuredData\Helper\Configuration\FaqPage $configuration
    ) {
        $this->structuredDataContainer = $structuredDataContainer;
        $this->faqPageDataProvider = $faqPageDataProvider;
        $this->configuration = $configuration;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        if (!$this->configuration->isEnabled()) {
            return;
        }

        $faqPageData = $this->faqPageDataProvider->getFaqPageData();
        
        if (empty($faqPageData)) {
            return;
        }

        $this->structuredDataContainer->add($faqPageData, 'faqPage');
    }
}
