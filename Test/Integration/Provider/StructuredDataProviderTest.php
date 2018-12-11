<?php
namespace MageSuite\GoogleStructuredData\Test\Integration\Provider;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class StructuredDataProviderTest extends \PHPUnit\Framework\TestCase
{
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
        $this->productDataProvider = Bootstrap::getObjectManager()->get(\MageSuite\GoogleStructuredData\Provider\Data\Product::class);
        $this->registry = Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
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
    }
}