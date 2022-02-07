<?php

namespace MageSuite\GoogleStructuredData\Observer;

class AddStructuredDataToRenderedPage implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageSuite\GoogleStructuredData\Service\JsonLdCreator
     */
    protected $jsonLdCreator;

    public function __construct(\MageSuite\GoogleStructuredData\Service\JsonLdCreator $jsonLdCreator)
    {
        $this->jsonLdCreator = $jsonLdCreator;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $response = $observer->getResponse();

        $html = $response->getBody();

        if ($html == '') {
            return;
        }

        $renderedStructuredData = sprintf('%s</body>', $this->jsonLdCreator->getRenderedJsonLd());

        $html = str_replace('</body>', $renderedStructuredData, $html);

        $response->setBody($html);
    }
}
