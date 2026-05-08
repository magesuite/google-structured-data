<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Test\Integration\Provider\Data;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class AudienceTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository;
    protected ?\Magento\Store\Model\StoreManagerInterface $storeManager;
    protected ?\MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider;
    protected ?\Magento\Eav\Model\Config $eavConfig;
    protected ?\Magento\Framework\App\CacheInterface $cache;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->storeManager = $this->objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $this->productDataProvider = $this->objectManager->get(\MageSuite\GoogleStructuredData\Provider\Data\Product::class);
        $this->eavConfig = $this->objectManager->get(\Magento\Eav\Model\Config::class);
        $this->cache = $this->objectManager->get(\Magento\Framework\App\CacheInterface::class);
    }

    protected function tearDown(): void
    {
        $this->cache->clean([\MageSuite\GoogleStructuredData\Provider\Data\Product::CACHE_GROUP]);
    }

    /**
     * @magentoDataFixture MageSuite_GoogleStructuredData::Test/Integration/_files/products_simple.php
     * @magentoConfigFixture current_store structured_data/product_page/audience/is_enabled 1
     * @magentoConfigFixture current_store structured_data/product_page/audience/suggested_min_age 5
     */
    public function testAudienceUsesGlobalMinAgeWhenAttributeNotConfigured(): void
    {
        $product = $this->productRepository->get('simple');
        $productData = $this->productDataProvider->getProductData($product, $this->storeManager->getStore());

        $this->assertEquals(5.0, $productData['audience']['suggestedMinAge']);
    }

    /**
     * @magentoDataFixture MageSuite_GoogleStructuredData::Test/Integration/_files/test_age_attr.php
     * @magentoDataFixture MageSuite_GoogleStructuredData::Test/Integration/_files/products_simple.php
     * @magentoConfigFixture current_store structured_data/product_page/audience/is_enabled 1
     * @magentoConfigFixture current_store structured_data/product_page/audience/suggested_min_age 5
     * @magentoConfigFixture current_store structured_data/product_page/audience/suggested_min_age_attribute test_age_attr
     */
    public function testAudienceFallsBackToGlobalMinAgeWhenProductAttributeEmpty(): void
    {
        $product = $this->productRepository->get('simple');
        $productData = $this->productDataProvider->getProductData($product, $this->storeManager->getStore());

        $this->assertEquals(5.0, $productData['audience']['suggestedMinAge']);
    }

    /**
     * @magentoDataFixture MageSuite_GoogleStructuredData::Test/Integration/_files/test_age_attr.php
     * @magentoDataFixture MageSuite_GoogleStructuredData::Test/Integration/_files/products_simple.php
     * @magentoConfigFixture current_store structured_data/product_page/audience/is_enabled 1
     * @magentoConfigFixture current_store structured_data/product_page/audience/suggested_min_age 5
     * @magentoConfigFixture current_store structured_data/product_page/audience/suggested_min_age_attribute test_age_attr
     */
    public function testAudienceReadsMinAgeFromProductAttribute(): void
    {
        $optionId = $this->getOptionIdByLabel('test_age_attr', '3-6');
        $product = $this->productRepository->get('simple');
        $product->setCustomAttribute('test_age_attr', $optionId);
        $this->productRepository->save($product);

        $product = $this->productRepository->get('simple', false, null, true);
        $productData = $this->productDataProvider->getProductData($product, $this->storeManager->getStore());

        $this->assertEquals(3.0, $productData['audience']['suggestedMinAge']);
    }

    protected function getOptionIdByLabel(string $attributeCode, string $label): string
    {
        $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);

        foreach ($attribute->getSource()->getAllOptions() as $option) {
            if ($option['label'] === $label) {
                return (string)$option['value'];
            }
        }

        throw new \Magento\Framework\Exception\LocalizedException(__("Option '%1' not found for attribute '%2'", $label, $attributeCode));
    }
}
