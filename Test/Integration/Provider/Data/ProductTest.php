<?php

namespace MageSuite\GoogleStructuredData\Test\Integration\Provider\Data;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class ProductTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;

    protected ?\MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider;

    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository;

    protected ?\Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory;

    protected ?\Magento\Framework\App\CacheInterface $cache;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->productDataProvider = $this->objectManager->get(\MageSuite\GoogleStructuredData\Provider\Data\Product::class);
        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->reviewCollectionFactory = $this->objectManager->create(\Magento\Review\Model\ResourceModel\Review\CollectionFactory::class);
        $this->cache = $this->objectManager->create(\Magento\Framework\App\CacheInterface::class);
    }

    public function tearDown(): void
    {
        $this->cache->clean([\MageSuite\GoogleStructuredData\Provider\Data\Product::CACHE_GROUP]);
    }

    /**
     * @magentoDataFixture MageSuite_GoogleStructuredData::Test/Integration/_files/products_simple.php
     */
    public function testSimpleProductData()
    {
        $expectedData = [
            '@context' => 'http://schema.org/',
            '@type' => 'Product',
            'name' => 'Simple Product',
            'image' => [],
            'description' => 'Description with &lt;b&gt;html tag&lt;/b&gt;',
            'sku' => 'simple',
            'url' => 'http://localhost/index.php/simple-product.html',
            'itemCondition' => 'NewCondition'
        ];
        $expectedOfferData = [
            '@type' => 'Offer',
            'sku' => 'simple',
            'price' => '10.00',
            'priceCurrency' => 'USD',
            'availability' => 'InStock',
            'url' => 'http://localhost/index.php/simple-product.html'
        ];

        $product = $this->productRepository->get('simple');
        $productData = $this->productDataProvider->execute($product);

        foreach ($expectedData as $key => $data) {
            $this->assertEquals($data, $productData[$key]);
        }
        foreach ($expectedOfferData as $key => $data) {
            $this->assertEquals($data, $productData['offers'][$key]);
        }
    }

    /**
     * @magentoDataFixture MageSuite_GoogleStructuredData::Test/Integration/_files/products_simple.php
     */
    public function testProductDataWithSpecialPrice()
    {
        $expectedData = [
            '@context' => 'http://schema.org/',
            '@type' => 'Product',
            'name' => 'Simple Product with Special Price',
            'image' => [],
            'description' => 'Description with &lt;b&gt;html tag&lt;/b&gt;',
            'sku' => 'simple_special_price',
            'url' => 'http://localhost/index.php/simple-product-with-special-price.html',
            'itemCondition' => 'NewCondition'
        ];
        $expectedOfferData = [
            '@type' => 'Offer',
            'sku' => 'simple_special_price',
            'price' => '5.00',
            'priceCurrency' => 'USD',
            'priceValidUntil' => date('Y-m-d', strtotime('+1 day')),
            'availability' => 'InStock',
            'url' => 'http://localhost/index.php/simple-product-with-special-price.html'
        ];

        $product = $this->productRepository->get('simple_special_price');
        $productData = $this->productDataProvider->execute($product);

        foreach ($expectedData as $key => $data) {
            $this->assertEquals($data, $productData[$key]);
        }
        foreach ($expectedOfferData as $key => $data) {
            $this->assertEquals($data, $productData['offers'][$key]);
        }
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture MageSuite_GoogleStructuredData::Test/Integration/_files/reviews_multistore.php
     */
    public function testProductDataWithReviews()
    {
        $product = $this->productRepository->get('simple');
        $productData = $this->productDataProvider->execute($product);

        $reviewCollection = $this->reviewCollectionFactory->create();
        $reviewCollection->addStoreFilter($product->getStoreId());

        $this->assertEquals(2, count($productData['review']));
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     */
    public function testConfigurableProductData()
    {
        $expectedData = [
            '@context' => 'http://schema.org/',
            '@type' => 'Product',
            'name' => 'Configurable Product',
            'image' => [],
            'sku' => 'configurable',
            'url' => 'http://localhost/index.php/configurable-product.html',
            'itemCondition' => 'NewCondition'
        ];
        $expectedOffersCount = 2;

        $product = $this->productRepository->get('configurable');
        $productData = $this->productDataProvider->execute($product);

        $this->assertEquals($expectedOffersCount, count($productData['offers']));
        foreach ($expectedData as $key => $data) {
            $this->assertEquals($data, $productData[$key]);
        }
    }

    /**
     * @magentoDataFixture Magento/GroupedProduct/_files/product_grouped.php
     */
    public function testGroupedProductData()
    {
        $expectedProductCounts = 2;
        $expectedSimpleProductData = [
            '@context' => 'http://schema.org/',
            '@type' => 'Product',
            'name' => 'Simple Product',
            'image' => [],
            'sku' => 'simple',
            'url' => 'http://localhost/index.php/grouped-product.html',
            'itemCondition' => 'NewCondition',
        ];
        $expectedVirtualProductData = [
            '@context' => 'http://schema.org/',
            '@type' => 'Product',
            'name' => 'Virtual Product',
            'image' => [],
            'sku' => 'virtual-product',
            'url' => 'http://localhost/index.php/grouped-product.html',
            'itemCondition' => 'NewCondition',
        ];

        $product = $this->productRepository->get('grouped-product');
        $productData = $this->productDataProvider->execute($product);

        $this->assertEquals($expectedProductCounts, count($productData));
        $simpleProductKey = array_search('simple', array_column($productData, 'sku'));
        foreach ($expectedSimpleProductData as $key => $data) {
            $this->assertEquals($data, $productData[$simpleProductKey][$key]);
        }

        $virtualProductKey = array_search('virtual-product', array_column($productData, 'sku'));
        foreach ($expectedVirtualProductData as $key => $data) {
            $this->assertEquals($data, $productData[$virtualProductKey][$key]);
        }
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture MageSuite_GoogleStructuredData::Test/Integration/_files/products_simple.php
     * @magentoConfigFixture default_store structured_data/product_page/delivery_data/is_enabled 1
     * @magentoConfigFixture default_store structured_data/product_page/delivery_data/business_days 0,1,2,3,4,5,6
     * @magentoConfigFixture default_store structured_data/product_page/delivery_data/cutoff_time 23:45:00
     * @magentoConfigFixture default_store structured_data/product_page/delivery_data/handling_time_value 5-8
     * @magentoConfigFixture default_store structured_data/product_page/delivery_data/handling_time_unit_code d
     * @magentoConfigFixture default_store structured_data/product_page/delivery_data/transit_time_value 3
     * @magentoConfigFixture default_store structured_data/product_page/delivery_data/transit_time_unit_code d
     */
    public function testProductShippingDetails()
    {
        $expectedShippingDetails = [
            '@type' => 'OfferShippingDetails',
            "deliveryTime" =>  [
                "@type" => "ShippingDeliveryTime",
                "businessDays" => [
                    "@type" => "OpeningHoursSpecification",
                    "dayOfWeek" => [
                        "https://schema.org/Monday",
                        "https://schema.org/Tuesday",
                        "https://schema.org/Wednesday",
                        "https://schema.org/Thursday",
                        "https://schema.org/Friday",
                        "https://schema.org/Saturday",
                        "https://schema.org/Sunday"
                    ]
                ],
                "cutoffTime" => "23:45:00-08:00",
                "handlingTime" => [
                    "@type" => "QuantitativeValue",
                    "minValue" => "5",
                    "maxValue" => "8",
                    "unitCode" => "d"
                ],
                "transitTime" => [
                    "@type" => "QuantitativeValue",
                    "minValue" => "3",
                    "maxValue" => "3",
                    "unitCode" => "d"
                ]
            ],
            "shippingRate" => [
                "@type" => "MonetaryAmount",
                "value" => 5.00,
                "currency" => "USD"
            ],
            "shippingDestination" => [
                "@type" => "DefinedRegion",
                "addressCountry" => "US"
            ]
        ];

        $product = $this->productRepository->get('simple_special_price');
        $productData = $this->productDataProvider->execute($product);

        $shippingDetails = array_shift($productData['offers']['shippingDetails']);
        foreach ($expectedShippingDetails as $key => $data) {
            $this->assertEquals($data, $shippingDetails[$key]);
        }
    }
}
