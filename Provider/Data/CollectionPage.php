<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data;

class CollectionPage
{
    protected const REFERENCED_NODES = [
        'isPartOf' => 'website',
        'publisher' => 'organization',
        'breadcrumb' => 'breadcrumbs',
        'mainEntity' => 'item_list'
    ];

    public function __construct(
        protected \MageSuite\GoogleStructuredData\Helper\Configuration $configuration,
        protected \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer
    ) {}

    public function getCollectionPageData(\Magento\Catalog\Model\Category $category, string $pageUrl): array
    {
        $collectionPageData = [
            '@type' => 'CollectionPage',
            '@id' => sprintf('%s#webpage', $pageUrl),
            'url' => $pageUrl
        ];

        $name = $this->resolveName($category);

        if ($name !== '') {
            $collectionPageData['name'] = $name;
        }

        $description = $this->resolveDescription($category);

        if ($description !== '') {
            $collectionPageData['description'] = $description;
        }

        $locale = $this->configuration->getLocale();

        if ($locale !== '') {
            $collectionPageData['inLanguage'] = $locale;
        }

        return array_merge($collectionPageData, $this->getReferences());
    }

    protected function resolveName(\Magento\Catalog\Model\Category $category): string
    {
        $metaTitle = trim((string)$category->getMetaTitle());

        if ($metaTitle !== '') {
            return $metaTitle;
        }

        return trim((string)$category->getName());
    }

    protected function resolveDescription(\Magento\Catalog\Model\Category $category): string
    {
        return trim((string)$category->getMetaDescription());
    }

    protected function getReferences(): array
    {
        $structuredData = $this->structuredDataContainer->getStructuredData();
        $references = [];

        foreach (self::REFERENCED_NODES as $property => $node) {
            $referencedId = $structuredData[$node]['@id'] ?? null;

            if ($referencedId === null) {
                continue;
            }

            $references[$property] = ['@id' => $referencedId];
        }

        return $references;
    }
}
