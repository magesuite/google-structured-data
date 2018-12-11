<?php
namespace MageSuite\GoogleStructuredData\Test\Unit\Provider;

use Magento\TestFramework\Helper\Bootstrap;

class StructuredDataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \MageSuite\GoogleStructuredData\Provider\StructuredDataProvider
     */
    protected $structuredDataProvider;


    protected function setUp()
    {
        $this->structuredDataProvider = Bootstrap::getObjectManager()->get('\MageSuite\GoogleStructuredData\Provider\StructuredDataProvider');
    }

    public function testItAddDataCorrectly()
    {
        $structuredDataProvider = $this->structuredDataProvider;

        $expectedData = $this->getStructuredData();
        foreach ($expectedData as $node => $data) {
            $structuredDataProvider->add($data, $node);
        }

        $data = $structuredDataProvider->structuredData();

        $this->assertArrayHasKey('product', $data);
        $this->assertArrayHasKey('breadcrumbs', $data);
        $this->assertEquals($expectedData['product']['@type'], 'Product');
        $this->assertEquals(count($expectedData['product']['image']), 8);
        $this->assertEquals($expectedData['product']['url'], 'http://page.test/de_EUR/test-product.html');
        $this->assertEquals($expectedData['product']['offers']['sku'], '22616');
        $this->assertEquals($expectedData['breadcrumbs']['@type'], 'BreadcrumbList');
        $this->assertEquals(count($expectedData['breadcrumbs']['itemListElement']), 4);
    }

    public function testItAddKeyCorrectly()
    {
        $structuredDataProvider = $this->structuredDataProvider;


        $structuredDataProvider->addKey('product', 'additional_key', 'test value');

        $data = $structuredDataProvider->structuredData();

        $this->assertArrayHasKey('additional_key', $data['product']);
        $this->assertEquals('test value', $data['product']['additional_key']);
    }

    public function testItRemoveKeyCorrectly()
    {
        $structuredDataProvider = $this->structuredDataProvider;


        $structuredDataProvider->removeKey('product', 'additional_key');

        $data = $structuredDataProvider->structuredData();

        $this->assertArrayNotHasKey('additional_key', $data['product']);
    }

    protected function getStructuredData()
    {
        return [
            'product' => [
                "@context" => "http://schema.org/",
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
                        "datePublished" => "2018-10-10 14:30:26",
                        "description" => "Review description",
                        "name" => "Review title"
                    ],
                ]
            ],
            'breadcrumbs' => [
                "@context" => "http://schema.org",
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