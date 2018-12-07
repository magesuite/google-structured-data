<?php
namespace MageSuite\GoogleStructuredData\Observer;

class AddAdditionalDataToRenderedPage implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageSuite\GoogleStructuredData\Provider\StructuredDataProvider
     */
    private $structuredDataProvider;

    public function __construct(
        \MageSuite\GoogleStructuredData\Provider\StructuredDataProvider $structuredDataProvider
    )
    {
        $this->structuredDataProvider = $structuredDataProvider;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $response = $observer->getResponse();

        $html = $response->getBody();

        if ($html == '') {
            return;
        }

        $structuredData = $this->structuredDataProvider->structuredData();

        $additional = '<script type="application/ld+json">' . json_encode($structuredData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</script></body>';

        $html = str_replace('</body>', $additional, $html);

        $response->setBody($html);
    }
}