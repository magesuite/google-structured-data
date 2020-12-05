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
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->productDataProvider = $this->objectManager->get(\MageSuite\GoogleStructuredData\Provider\Data\Product::class);
        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->cache = $this->objectManager->create(\Magento\Framework\App\CacheInterface::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testItReturnProductDataCorrectly()
    {
        $product = $this->productRepository->get('simple');
        $productData = $this->productDataProvider->getProductStructuredData($product);

        $this->assertEquals($this->expectedData(), $productData);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testProductWithSpecialPriceData()
    {
        $product = $this->productRepository->get('simple');
        $product->setSpecialPrice(10);
        $product->setSpecialFromDate(date('Y-m-d', strtotime('-1 day')));
        $product->setSpecialToDate($this->getSpecialToDate());
        $productData = $this->productDataProvider->getProductStructuredData($product);
        $expectedData = $this->expectedData(true);

        $this->assertEquals($expectedData, $productData);
    }

    protected function expectedData($withSpecialPrice = false)
    {
        $data = [
            '@context' => "http://schema.org/",
            '@type' => "Product",
            'name' => "Simple Product",
            'image' => [],
            'description' => "Description with <b>html tag</b>",
            'sku' => "simple",
            'url' => "http://localhost/index.php/simple-product.html",
            'offers' => [
                '@type' => "Offer",
                'sku' => "simple",
                'price' => "10.00",
                'priceCurrency' => "USD",
                'availability' => "InStock",
                'url' => "http://localhost/index.php/simple-product.html",
            ],
            'itemCondition' => 'NewCondition'
        ];

        if ($withSpecialPrice) {
            $data['offers']['priceValidUntil'] = $this->getSpecialToDate();
        }

        return $data;
    }

    protected function getSpecialToDate()
    {
        return date('Y-m-d', strtotime('+1 day'));
    }

    public function tearDown(): void {
        $this->cache->clean([\MageSuite\GoogleStructuredData\Provider\Data\Product::CACHE_GROUP]);
    }
}
