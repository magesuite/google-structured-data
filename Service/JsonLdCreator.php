<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Service;

class JsonLdCreator
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer
    ) {}

    public function getRenderedJsonLd(): string
    {
        $structuredData = $this->structuredDataContainer->getStructuredData();

        $jsonLd = '';
        foreach ($structuredData as $data) {
            $jsonLd .= sprintf(
                '<script type="application/ld+json">%s</script>',
                json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG)
            );
        }

        return $jsonLd;
    }
}
