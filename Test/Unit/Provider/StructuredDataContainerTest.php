<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Test\Unit\Provider;

class StructuredDataContainerTest extends \PHPUnit\Framework\TestCase
{
    protected ?\MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer = null;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->structuredDataContainer = $objectManager->get(\MageSuite\GoogleStructuredData\Provider\StructuredDataContainer::class);
    }

    public function testItAddDataCorrectly(): void
    {
        $structuredDataContainer = $this->structuredDataContainer;

        $expectedData = $this->getStructuredData();
        foreach ($expectedData as $node => $data) {
            $structuredDataContainer->add($data, $node);
        }

        $data = $structuredDataContainer->getStructuredData();

        $this->assertArrayHasKey('product', $data);
        $this->assertArrayHasKey('breadcrumbs', $data);
        $this->assertEquals($expectedData['product']['@type'], 'Product');
        $this->assertEquals(count($expectedData['product']['image']), 8);
        $this->assertEquals($expectedData['product']['url'], 'http://page.test/de_EUR/test-product.html');
        $this->assertEquals($expectedData['product']['offers']['sku'], '22616');
        $this->assertEquals($expectedData['breadcrumbs']['@type'], 'BreadcrumbList');
        $this->assertEquals(count($expectedData['breadcrumbs']['itemListElement']), 4);
    }

    public function testItAddKeyCorrectly(): void
    {
        $structuredDataContainer = $this->structuredDataContainer;

        $structuredDataContainer->addKey('product', 'additional_key', 'test value');

        $data = $structuredDataContainer->getStructuredData();

        $this->assertArrayHasKey('additional_key', $data['product']);
        $this->assertEquals('test value', $data['product']['additional_key']);
    }

    public function testItRemoveKeyCorrectly(): void
    {
        $structuredDataContainer = $this->structuredDataContainer;

        $structuredDataContainer->removeKey('product', 'additional_key');

        $data = $structuredDataContainer->getStructuredData();

        $this->assertArrayNotHasKey('additional_key', $data['product']);
    }

    protected function getStructuredData(): array
    {
        return [
            'product' => [
                "@context" => "https://schema.org/",
                "@type" => "Product",
                "name" => "Test Structured Product",
                "image" => [
                    "http://page.test/media/catalog/product/1/0/10062-0-1537791475_1.jpeg",
                    "http://page.test/media/catalog/product/1/0/10062-0-1538423756_1.jpeg",
                    "http://page.test/media/catalog/product/1/0/10062-1-1537791475_1.jpeg",
                    "http://page.test/media/catalog/product/1/0/10062-1-1538423756_1.jpeg",
                    "http://page.test/media/catalog/product/1/0/10062-2-1537791475_1.jpeg",
                    "http://page.test/media/catalog/product/1/0/10062-2-1538423756_1.jpeg",
                    "http://page.test/media/catalog/product/1/0/10062-3-1537791475_1.jpeg",
                    "http://page.test/media/catalog/product/1/0/10062-3-1538423756_1.jpeg"
                ],
                "description" => "Test Description",
                "sku" => "22616",
                "url" => "http://page.test/de_EUR/test-product.html",
                "offers" => [
                    "@type" => "Offer",
                    "sku" => "22616",
                    "price" => "99.99",
                    "priceCurrency" => "EUR",
                    "availability" => "InStock",
                    "url" => "http://page.test/de_EUR/test-product.html"
                ],
                "aggregateRating" => [
                    "@type" => "AggregateRating",
                    "ratingValue" => 4.7,
                    "reviewCount" => "7"
                ],
                "review" => [
                    [
                        "@type" => "Review",
                        "author" => "Test User",
                        "datePublished" => "2016-04-09T14:30:00+02:00",
                        "description" => "Review description",
                        "name" => "Review title"
                    ],
                ]
            ],
            'breadcrumbs' => [
                "@context" => "https://schema.org",
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'item' => [
                            '@id' => 'google.com',
                            'name' => 'google'
                        ]
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'item' => [
                            '@id' => 'google.com',
                            'name' => 'google'
                        ]
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 3,
                        'item' => [
                            '@id' => 'google.com',
                            'name' => 'google'
                        ]
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 4,
                        'item' => [
                            '@id' => 'google.com',
                            'name' => 'google'
                        ]
                    ],
                ]
            ]
        ];
    }
}
