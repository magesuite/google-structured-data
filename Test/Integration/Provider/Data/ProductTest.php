<?php

namespace MageSuite\GoogleStructuredData\Test\Integration\Provider;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class ProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageSuite\GoogleStructuredData\Provider\Data\Product
     */
    protected $productDataProvider;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Review\Model\ResourceModel\Review\Collection
     */
    protected $reviewCollectionFactory;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

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
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
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
            'itemCondition' => 'NewCondition',
            'offers' => [
                '@type' => 'Offer',
                'sku' => 'simple',
                'price' => '10.00',
                'priceCurrency' => 'USD',
                'availability' => 'InStock',
                'url' => 'http://localhost/index.php/simple-product.html',
            ]
        ];

        $product = $this->productRepository->get('simple');

        $productData = $this->productDataProvider->execute($product);

        foreach ($expectedData as $key => $data) {
            $this->assertEquals($data, $productData[$key]);
        }
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testProductDataWithSpecialPrice()
    {
        $expectedData = [
            '@context' => 'http://schema.org/',
            '@type' => 'Product',
            'name' => 'Simple Product',
            'image' => [],
            'description' => 'Description with &lt;b&gt;html tag&lt;/b&gt;',
            'sku' => 'simple',
            'url' => 'http://localhost/index.php/simple-product.html',
            'itemCondition' => 'NewCondition',
            'offers' => [
                '@type' => 'Offer',
                'sku' => 'simple',
                'price' => '5.00',
                'priceCurrency' => 'USD',
                'priceValidUntil' => date('Y-m-d', strtotime('+1 day')),
                'availability' => 'InStock',
                'url' => 'http://localhost/index.php/simple-product.html',
            ]
        ];

        $product = $this->productRepository->get('simple');
        $product->setSpecialPrice(5);
        $product->setSpecialFromDate(date('Y-m-d', strtotime('-1 day')));
        $product->setSpecialToDate(date('Y-m-d', strtotime('+1 day')));

        $productData = $this->productDataProvider->execute($product);

        foreach ($expectedData as $key => $data) {
            $this->assertEquals($data, $productData[$key]);
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
        $expectedData = [
            0 => [
                '@context' => 'http://schema.org/',
                '@type' => 'Product',
                'name' => 'Simple Product',
                'image' => [],
                'sku' => 'simple',
                'url' => 'http://localhost/index.php/grouped-product.html',
                'itemCondition' => 'NewCondition',
            ],
            1 => [
                '@context' => 'http://schema.org/',
                '@type' => 'Product',
                'name' => 'Virtual Product',
                'image' => [],
                'sku' => 'virtual-product',
                'url' => 'http://localhost/index.php/grouped-product.html',
                'itemCondition' => 'NewCondition',
            ]
        ];
        $expectedProductCounts = 2;

        $product = $this->productRepository->get('grouped-product');
        $productData = $this->productDataProvider->execute($product);

        $this->assertEquals($expectedProductCounts, count($productData));
        foreach ($expectedData as $productIndex => $expectedSingleData) {
            foreach ($expectedSingleData as $key => $data) {
                $this->assertEquals($data, $productData[$productIndex][$key]);
            }
        }
    }
}
