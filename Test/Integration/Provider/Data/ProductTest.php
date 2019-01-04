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
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->productDataProvider = $this->objectManager->get(\MageSuite\GoogleStructuredData\Provider\Data\Product::class);
        $this->registry = $this->objectManager->get(\Magento\Framework\Registry::class);
        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testItReturnProductDataCorrectly()
    {
        $product = $this->productRepository->get('simple');
        $registry = $this->registry;
        $registry->register('current_product', $product);

        $productData = $this->productDataProvider->getProductStructuredData();

        $this->assertEquals($this->expectedData(), $productData);
    }

    protected function expectedData()
    {
        return [
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
    }
}