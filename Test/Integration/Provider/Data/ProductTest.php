<?php

namespace MageSuite\GoogleStructuredData\Test\Integration\Provider\Data;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class ProductTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\Magento\Framework\App\CacheInterface $cache;
    protected ?\Magento\Store\Model\StoreManagerInterface $storeManager;
    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository;
    protected ?\Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory;
    protected ?\MageSuite\GoogleStructuredData\Model\Indexer\Product $indexer;
    protected ?\MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider;
    protected ?\MageSuite\GoogleStructuredData\Provider\Data\Product\Modifier\DeliveryData $deliveryDataModifier;
    protected ?\Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->cache = $this->objectManager->get(\Magento\Framework\App\CacheInterface::class);
        $this->storeManager = $this->objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->reviewCollectionFactory = $this->objectManager->get(\Magento\Review\Model\ResourceModel\Review\CollectionFactory::class);
        $this->indexer = $this->objectManager->get(\MageSuite\GoogleStructuredData\Model\Indexer\Product::class);
        $this->deliveryDataModifier = $this->objectManager->get(\MageSuite\GoogleStructuredData\Provider\Data\Product\Modifier\DeliveryData::class);
        $this->productDataProvider = $this->objectManager->get(\MageSuite\GoogleStructuredData\Provider\Data\Product::class);
        $this->timezone = $this->objectManager->get(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
    }

    public function tearDown(): void
    {
        $this->cache->clean([\MageSuite\GoogleStructuredData\Provider\Data\Product::CACHE_GROUP]);
    }

    /**
     * @magentoDataFixture MageSuite_GoogleStructuredData::Test/Integration/_files/products_simple.php
     */
    public function testSimpleProductData(): void
    {
        $expectedData = [
            '@type' => 'Product',
            'name' => 'Simple Product',
            'image' => [],
            'description' => 'Description with html tag',
            'sku' => 'simple',
            'url' => 'http://localhost/index.php/simple-product.html',
            'itemCondition' => \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverInterface::CONTEXT . 'NewCondition'
        ];
        $expectedOfferData = [
            '@type' => 'Offer',
            'sku' => 'simple',
            'price' => '10.00',
            'priceCurrency' => 'USD',
            'availability' => \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverInterface::CONTEXT . 'InStock',
            'url' => 'http://localhost/index.php/simple-product.html'
        ];

        $product = $this->productRepository->get('simple');
        $this->indexer->executeRow($product->getId());
        $productData = $this->productDataProvider->getProductData($product, $this->storeManager->getStore());

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
    public function testCacheKeyIsCurrencySpecific(): void
    {
        $product = $this->productRepository->get('simple');
        $store = $this->storeManager->getStore();

        $cacheKey = $this->productDataProvider->getCacheKey($product, $store);

        $this->assertStringEndsWith('_' . $store->getCurrentCurrencyCode(), $cacheKey);
    }

    /**
     * @magentoDataFixture MageSuite_GoogleStructuredData::Test/Integration/_files/products_simple.php
     */
    public function testProductIdIgnoresCategoryContextUrl(): void
    {
        $product = $this->productRepository->get('simple');
        $product->setData('request_path', 'some-category/simple-product.html');

        $productData = $this->productDataProvider->generateProductData($product, $this->storeManager->getStore());

        $this->assertStringEndsWith('/simple-product.html#product', $productData['@id']);
        $this->assertStringNotContainsString('some-category', $productData['@id']);
    }

    #[\Magento\TestFramework\Fixture\DataFixture('MageSuite_GoogleStructuredData::Test/Integration/_files/products_simple.php')]
    public function testProductIdIsOmittedWhenProductHasNoUrlRewrite(): void
    {
        $product = $this->productRepository->get('simple');

        $this->objectManager->get(\Magento\UrlRewrite\Model\UrlPersistInterface::class)->deleteByData([
            \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::ENTITY_ID => $product->getId(),
            \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::ENTITY_TYPE => \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator::ENTITY_TYPE
        ]);
        $this->objectManager->get(\MageSuite\GoogleStructuredData\Model\Catalog\BatchProductUrlData::class)->reset();

        $productData = $this->productDataProvider->generateProductData($product, $this->storeManager->getStore());

        $this->assertArrayNotHasKey('@id', $productData);
    }

    /**
     * @magentoDataFixture MageSuite_GoogleStructuredData::Test/Integration/_files/products_simple.php
     */
    public function testProductDataWithSpecialPrice(): void
    {
        $expectedData = [
            '@type' => 'Product',
            'name' => 'Simple Product with Special Price',
            'image' => [],
            'description' => 'Description with html tag',
            'sku' => 'simple_special_price',
            'url' => 'http://localhost/index.php/simple-product-with-special-price.html',
            'itemCondition' => \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverInterface::CONTEXT . 'NewCondition'
        ];
        $expectedOfferData = [
            '@type' => 'Offer',
            'sku' => 'simple_special_price',
            'price' => '5.00',
            'priceCurrency' => 'USD',
            'priceValidUntil' => date('Y-m-d', strtotime('+1 day')),
            'availability' => \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverInterface::CONTEXT . 'InStock',
            'url' => 'http://localhost/index.php/simple-product-with-special-price.html'
        ];

        $product = $this->productRepository->get('simple_special_price');
        $this->indexer->executeRow($product->getId());
        $productData = $this->productDataProvider->getProductData($product, $this->storeManager->getStore());

        foreach ($expectedData as $key => $data) {
            $this->assertEquals($data, $productData[$key]);
        }
        foreach ($expectedOfferData as $key => $data) {
            $this->assertEquals($data, $productData['offers'][$key]);
        }
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture MageSuite_GoogleStructuredData::Test/Integration/_files/products_simple.php
     * @magentoConfigFixture default_store structured_data/product_page/default_price_valid_until_enabled 1
     * @magentoConfigFixture base_website general/locale/timezone UTC
     */
    public function testDefaultPriceValidUntilIsAppliedWhenEnabled(): void
    {
        $product = $this->productRepository->get('simple');
        $this->indexer->executeRow($product->getId());
        $store = $this->storeManager->getStore();
        $productData = $this->productDataProvider->getProductData($product, $store);
        $expectedDate = $this->timezone->scopeDate($store)->modify('+1 year')->format('Y-m-d');

        $this->assertEquals($expectedDate, $productData['offers']['priceValidUntil']);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture MageSuite_GoogleStructuredData::Test/Integration/_files/products_simple.php
     * @magentoConfigFixture default_store structured_data/product_page/default_price_valid_until_enabled 1
     * @magentoConfigFixture base_website general/locale/timezone UTC
     */
    public function testDefaultPriceValidUntilDoesNotOverrideSpecialToDate(): void
    {
        $product = $this->productRepository->get('simple_special_price');
        $this->indexer->executeRow($product->getId());
        $productData = $this->productDataProvider->getProductData($product, $this->storeManager->getStore());

        $this->assertEquals(date('Y-m-d', strtotime('+1 day')), $productData['offers']['priceValidUntil']);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture MageSuite_GoogleStructuredData::Test/Integration/_files/reviews_multistore.php
     */
    public function testProductDataWithReviews(): void
    {
        $product = $this->productRepository->get('simple');
        $this->indexer->executeRow($product->getId());
        $productData = $this->productDataProvider->getProductData($product, $this->storeManager->getStore());

        $reviewCollection = $this->reviewCollectionFactory->create();
        $reviewCollection->addStoreFilter($product->getStoreId());

        $this->assertEquals(2, count($productData['review']));
    }

    /**
     * @magentoDataFixture MageSuite_GoogleStructuredData::Test/Integration/_files/configurable_attribute.php
     * @magentoDataFixture MageSuite_GoogleStructuredData::Test/Integration/_files/configurable_products.php
     * @magentoConfigFixture default_store structured_data/product_page/configurable/use_parent_product_url 0
     */
    public function testConfigurableProductData(): void
    {
        $product = $this->productRepository->get('configurable');
        $simpleProducts = $product->getTypeInstance()->getUsedProducts($product);

        $expectedProductGroupData = [
            '@type' => 'ProductGroup',
            'name' => 'Configurable Product',
            'productGroupID' => 'configurable',
            'url' => 'http://localhost/index.php/configurable-product.html',
            'variesBy' => ['https://schema.org/color'],
            'description' => '',
            'hasVariant' => [
                [
                    '@type' => 'Product',
                    '@id' => $simpleProducts[0]->getProductUrl() . '#product',
                    'name' => 'Configurable OptionOption 1',
                    'sku' => 'simple_10',
                    'url' => 'http://localhost/index.php/configurable-product.html',
                    'itemCondition' => \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverInterface::CONTEXT . 'NewCondition',
                    'color' => 'Option 1',
                    'image' => [],
                    'offers' => [
                        '@type' => 'Offer',
                        'sku' => 'simple_10',
                        'price' => '10.00',
                        'priceCurrency' => 'USD',
                        'availability' => \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverInterface::CONTEXT . 'InStock',
                        'url' => $simpleProducts[0]->getProductUrl()
                    ],
                    'description' => null
                ],
                [
                    '@type' => 'Product',
                    '@id' => $simpleProducts[1]->getProductUrl() . '#product',
                    'name' => 'Configurable OptionOption 2',
                    'sku' => 'simple_20',
                    'url' => 'http://localhost/index.php/configurable-product.html',
                    'itemCondition' => \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverInterface::CONTEXT . 'NewCondition',
                    'color' => 'Option 2',
                    'image' => [],
                    'offers' => [
                        '@type' => 'Offer',
                        'sku' => 'simple_20',
                        'price' => '20.00',
                        'priceCurrency' => 'USD',
                        'availability' => \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverInterface::CONTEXT . 'InStock',
                        'url' => $simpleProducts[1]->getProductUrl()
                    ],
                    'description' => null
                ]
            ]
        ];

        $expectedData = [
            '@type' => 'Product',
            'name' => 'Configurable Product',
            'image' => [],
            'sku' => 'configurable',
            'url' => 'http://localhost/index.php/configurable-product.html',
            'itemCondition' => \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverInterface::CONTEXT . 'NewCondition'
        ];
        $expectedOffersCount = 2;
        $this->indexer->executeRow($product->getId());
        $productData = $this->productDataProvider->getProductData($product, $this->storeManager->getStore());

        $this->assertEquals($expectedProductGroupData, $productData[0]);
        $this->assertEquals($expectedOffersCount, count($productData[1]['offers']));
        foreach ($expectedData as $key => $data) {
            $this->assertEquals($data, $productData[1][$key]);
        }
    }

    /**
     * @magentoDataFixture Magento/GroupedProduct/_files/product_grouped.php
     */
    public function testGroupedProductData(): void
    {
        $expectedProductCounts = 2;
        $expectedSimpleProductData = [
            '@type' => 'Product',
            'name' => 'Simple Product',
            'image' => [],
            'sku' => 'simple',
            'url' => 'http://localhost/index.php/grouped-product.html',
            'itemCondition' => \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverInterface::CONTEXT . 'NewCondition',
        ];
        $expectedVirtualProductData = [
            '@type' => 'Product',
            'name' => 'Virtual Product',
            'image' => [],
            'sku' => 'virtual-product',
            'url' => 'http://localhost/index.php/grouped-product.html',
            'itemCondition' => \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverInterface::CONTEXT . 'NewCondition',
        ];

        $product = $this->productRepository->get('grouped-product');
        $this->indexer->executeRow($product->getId());
        $productData = $this->productDataProvider->getProductData($product, $this->storeManager->getStore());

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

    #[\Magento\TestFramework\Fixture\DataFixture('Magento/GroupedProduct/_files/product_grouped.php')]
    public function testGroupedVariantIdsAreAnchoredToParentUrl(): void
    {
        $product = $this->productRepository->get('grouped-product');

        $productData = $this->productDataProvider->generateProductData($product, $this->storeManager->getStore());
        $nodeIds = array_column($productData, '@id');

        $this->assertCount(count($productData), $nodeIds);
        $this->assertSame($nodeIds, array_unique($nodeIds));

        foreach ($nodeIds as $nodeId) {
            $this->assertStringStartsWith('http://localhost/index.php/grouped-product.html#product-', $nodeId);
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
     * @magentoConfigFixture base_website general/locale/timezone UTC
     */
    public function testProductShippingDetails(): void
    {
        if (!$this->deliveryDataModifier->isEnabled()) {
            $this->markTestSkipped('Modifier is disabled.');
        }

        $expectedShippingDetails = [
            '@type' => 'OfferShippingDetails',
            "deliveryTime" => [
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
                "cutoffTime" => "23:45:00+00:00",
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
        $this->indexer->executeRow($product->getId());
        $productData = $this->productDataProvider->getProductData($product, $this->storeManager->getStore());

        $shippingDetails = array_shift($productData['offers']['shippingDetails']);
        foreach ($expectedShippingDetails as $key => $data) {
            $this->assertEquals($data, $shippingDetails[$key]);
        }
    }
}
