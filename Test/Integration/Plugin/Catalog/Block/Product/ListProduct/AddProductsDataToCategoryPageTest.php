<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Test\Integration\Plugin\Catalog\Block\Product\ListProduct;

#[\Magento\TestFramework\Fixture\AppIsolation(true)]
#[\Magento\TestFramework\Fixture\DbIsolation(true)]
class AddProductsDataToCategoryPageTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository;
    protected ?\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory;
    protected ?\Magento\Framework\Registry $registry;
    protected ?\MageSuite\GoogleStructuredData\Plugin\Catalog\Block\Product\ListProduct\AddProductsDataToCategoryPage $plugin;
    protected ?\MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer;
    protected ?\Magento\Eav\Model\Config $eavConfig;
    protected ?\Magento\Framework\App\CacheInterface $cache;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->collectionFactory = $this->objectManager->get(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class);
        $this->registry = $this->objectManager->get(\Magento\Framework\Registry::class);
        $this->plugin = $this->objectManager->get(\MageSuite\GoogleStructuredData\Plugin\Catalog\Block\Product\ListProduct\AddProductsDataToCategoryPage::class);
        $this->structuredDataContainer = $this->objectManager->get(\MageSuite\GoogleStructuredData\Provider\StructuredDataContainer::class);
        $this->eavConfig = $this->objectManager->get(\Magento\Eav\Model\Config::class);
        $this->cache = $this->objectManager->get(\Magento\Framework\App\CacheInterface::class);
    }

    protected function tearDown(): void
    {
        $this->cache->clean([\MageSuite\GoogleStructuredData\Provider\Data\Product::CACHE_GROUP]);
        $this->registry->unregister('current_category');
    }

    /**
     * @magentoDataFixture MageSuite_GoogleStructuredData::Test/Integration/_files/test_age_attr.php
     * @magentoDataFixture MageSuite_GoogleStructuredData::Test/Integration/_files/products_simple.php
     * @magentoConfigFixture current_store structured_data/category_page/include_products 1
     * @magentoConfigFixture current_store structured_data/product_page/is_indexing_enabled 0
     * @magentoConfigFixture current_store structured_data/product_page/audience/is_enabled 1
     * @magentoConfigFixture current_store structured_data/product_page/audience/suggested_min_age 5
     * @magentoConfigFixture current_store structured_data/product_page/audience/suggested_min_age_attribute test_age_attr
     */
    public function testProductAudienceAttributeIsReadFromCollectionWhenIndexingDisabled(): void
    {
        $optionId = $this->getOptionIdByLabel('test_age_attr', '3-6');
        $product = $this->productRepository->get('simple');
        $product->setCustomAttribute('test_age_attr', $optionId);
        $this->productRepository->save($product);

        $category = $this->objectManager->create(\Magento\Catalog\Model\Category::class);
        $category->setId(2);
        $this->registry->register('current_category', $category);

        $collection = $this->collectionFactory->create();
        $collection->addAttributeToFilter('sku', 'simple');

        $listProductBlock = $this->objectManager->create(\Magento\Catalog\Block\Product\ListProduct::class);
        $this->plugin->afterGetLoadedProductCollection($listProductBlock, $collection);

        $structuredData = $this->structuredDataContainer->getStructuredData();

        $productData = reset($structuredData);
        $this->assertNotEmpty($productData);
        $this->assertEquals(3.0, $productData['audience']['suggestedMinAge']);
    }

    /**
     * @magentoDataFixture MageSuite_GoogleStructuredData::Test/Integration/_files/test_age_attr.php
     * @magentoDataFixture MageSuite_GoogleStructuredData::Test/Integration/_files/products_simple.php
     * @magentoConfigFixture current_store structured_data/category_page/include_products 1
     * @magentoConfigFixture current_store structured_data/product_page/is_indexing_enabled 0
     * @magentoConfigFixture current_store structured_data/product_page/audience/is_enabled 1
     * @magentoConfigFixture current_store structured_data/product_page/audience/suggested_min_age 5
     * @magentoConfigFixture current_store structured_data/product_page/audience/suggested_min_age_attribute test_age_attr
     */
    public function testProductAudienceFallsBackToGlobalWhenAttributeNotSetOnProduct(): void
    {
        $category = $this->objectManager->create(\Magento\Catalog\Model\Category::class);
        $category->setId(2);
        $this->registry->register('current_category', $category);

        $collection = $this->collectionFactory->create();
        $collection->addAttributeToFilter('sku', 'simple');

        $listProductBlock = $this->objectManager->create(\Magento\Catalog\Block\Product\ListProduct::class);
        $this->plugin->afterGetLoadedProductCollection($listProductBlock, $collection);

        $structuredData = $this->structuredDataContainer->getStructuredData();

        $productData = reset($structuredData);
        $this->assertNotEmpty($productData);
        $this->assertEquals(5.0, $productData['audience']['suggestedMinAge']);
    }

    protected function getOptionIdByLabel(string $attributeCode, string $label): string
    {
        $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);

        foreach ($attribute->getSource()->getAllOptions() as $option) {
            if ($option['label'] === $label) {
                return (string)$option['value'];
            }
        }

        throw new \Magento\Framework\Exception\LocalizedException(
            __("Option '%1' not found for attribute '%2'", $label, $attributeCode)
        );
    }
}
