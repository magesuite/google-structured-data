<?php
namespace MageSuite\GoogleStructuredData\Observer;

class AddStructuredDataToRenderedPage implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageSuite\GoogleStructuredData\Service\JsonLdCreator
     */
    private $jsonLdCreator;

    public function __construct(\MageSuite\GoogleStructuredData\Service\JsonLdCreator $jsonLdCreator)
    {
        $this->jsonLdCreator = $jsonLdCreator;
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

        $renderedStructuredData = $this->jsonLdCreator->getRenderedJsonLd() . '</body>';

        $html = str_replace('</body>', $renderedStructuredData, $html);

        $response->setBody($html);
    }
}