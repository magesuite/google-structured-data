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

        if (empty($structuredData)) {
            return '';
        }

        $document = [
            '@context' => 'https://schema.org',
            '@graph' => $this->buildGraph($structuredData)
        ];

        return sprintf(
            '<script type="application/ld+json">%s</script>',
            json_encode($document, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG)
        );
    }

    protected function buildGraph(array $structuredData): array
    {
        $graph = [];

        foreach ($structuredData as $node) {
            if (is_array($node) && array_is_list($node)) {
                foreach ($node as $entity) {
                    $graph[] = $entity;
                }

                continue;
            }

            $graph[] = $node;
        }

        return $graph;
    }
}
