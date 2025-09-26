<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Observer;

class AddStructuredDataToRenderedPage implements \Magento\Framework\Event\ObserverInterface
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Service\JsonLdCreator $jsonLdCreator
    ) {}

    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        $response = $observer->getResponse();

        $html = $response->getBody();

        if ($html === '') {
            return;
        }

        $renderedStructuredData = sprintf('%s</body>', $this->jsonLdCreator->getRenderedJsonLd());

        $html = str_replace('</body>', $renderedStructuredData, $html);

        $response->setBody($html);
    }
}
