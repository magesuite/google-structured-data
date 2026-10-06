<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Test\Integration\Provider\Data\Product\Attribute;

#[\Magento\TestFramework\Fixture\DbIsolation(true)]
#[\Magento\TestFramework\Fixture\AppIsolation(true)]
class BrandTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
    }

    #[\Magento\TestFramework\Fixture\DataFixture('MageSuite_GoogleStructuredData::Test/Integration/_files/test_brand_attr.php')]
    #[\Magento\TestFramework\Fixture\DataFixture('MageSuite_GoogleStructuredData::Test/Integration/_files/products_simple.php')]
    public function testItReturnsOnlyFirstBrandNameOfMultiselectAttributeWithCustomSource(): void
    {
        $product = $this->productRepository->get('simple');
        $product->setCustomAttribute('test_brand_attr', sprintf(
            '%d,%d',
            \MageSuite\GoogleStructuredData\Test\Integration\Stub\BrandSourceModel::SECOND_BRAND_ID,
            \MageSuite\GoogleStructuredData\Test\Integration\Stub\BrandSourceModel::FIRST_BRAND_ID
        ));
        $this->productRepository->save($product);
        $product = $this->productRepository->get('simple', false, null, true);
        $brand = $this->objectManager->create(\MageSuite\GoogleStructuredData\Provider\Data\Product\Attribute\Brand::class);

        $this->assertSame(
            ['@type' => 'Brand', 'name' => 'Second Brand'],
            $brand->getAttributeData($product, 'test_brand_attr')
        );
    }
}
