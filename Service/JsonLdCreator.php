<?php

namespace MageSuite\GoogleStructuredData\Service;

class JsonLdCreator
{
    /**
     * @var \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer
     */
    protected $structuredDataContainer;

    public function __construct(\MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer)
    {
        $this->structuredDataContainer = $structuredDataContainer;
    }

    public function getRenderedJsonLd()
    {
        $structuredData = $this->structuredDataContainer->getStructuredData();

        $jsonLd = '';
        foreach ($structuredData as $data) {
            $jsonLd .= sprintf('<script type="application/ld+json">%s</script>', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        return $jsonLd;
    }
}
