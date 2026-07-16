<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Test\Unit\Service;

class JsonLdCreatorTest extends \PHPUnit\Framework\TestCase
{
    public function testItRendersAllEntitiesInSingleGraphScript(): void
    {
        $creator = $this->createCreator([
            'organization' => [
                '@type' => 'Organization',
                '@id' => 'http://shop.test/#organization',
                'name' => 'Shop'
            ],
            'item_list' => [
                '@type' => 'ItemList',
                '@id' => 'http://shop.test/category.html#productlist',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'url' => 'http://shop.test/p1.html']
                ]
            ]
        ]);

        $output = $creator->getRenderedJsonLd();

        $this->assertEquals(1, substr_count($output, '<script type="application/ld+json">'));

        $decoded = $this->decode($output);
        $this->assertSame('https://schema.org', $decoded['@context']);
        $this->assertArrayHasKey('@graph', $decoded);
        $this->assertCount(2, $decoded['@graph']);

        foreach ($decoded['@graph'] as $node) {
            $this->assertArrayNotHasKey('@context', $node);
        }
    }

    public function testItFlattensMultiNodeContainerEntriesIntoSeparateGraphNodes(): void
    {
        $creator = $this->createCreator([
            'product' => [
                ['@type' => 'ProductGroup', 'name' => 'Group'],
                ['@type' => 'Product', 'name' => 'Variant']
            ],
            'organization' => [
                '@type' => 'Organization',
                'name' => 'Shop'
            ]
        ]);

        $decoded = $this->decode($creator->getRenderedJsonLd());

        $this->assertCount(3, $decoded['@graph']);
        $this->assertEqualsCanonicalizing(
            ['ProductGroup', 'Product', 'Organization'],
            array_column($decoded['@graph'], '@type')
        );
    }

    public function testItReturnsEmptyStringWhenNoData(): void
    {
        $creator = $this->createCreator([]);

        $this->assertSame('', $creator->getRenderedJsonLd());
    }

    protected function createCreator(array $nodes): \MageSuite\GoogleStructuredData\Service\JsonLdCreator
    {
        $container = new \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer();

        foreach ($nodes as $node => $data) {
            $container->add($data, $node);
        }

        return new \MageSuite\GoogleStructuredData\Service\JsonLdCreator($container);
    }

    protected function decode(string $output): array
    {
        $json = str_replace(['<script type="application/ld+json">', '</script>'], '', $output);

        return json_decode($json, true);
    }
}
