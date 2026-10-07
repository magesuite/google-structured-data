<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Test\Integration\Model\Eav;

#[\Magento\TestFramework\Fixture\DbIsolation(true)]
#[\Magento\TestFramework\Fixture\AppIsolation(true)]
class GetAttributeValueTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository;
    protected ?\Magento\Eav\Model\Config $eavConfig;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->eavConfig = $this->objectManager->get(\Magento\Eav\Model\Config::class);
    }

    #[\Magento\TestFramework\Fixture\DataFixture('MageSuite_GoogleStructuredData::Test/Integration/_files/test_custom_source_attr.php')]
    #[\Magento\TestFramework\Fixture\DataFixture('MageSuite_GoogleStructuredData::Test/Integration/_files/products_simple.php')]
    public function testItReturnsLabelResolvedByCustomSourceModelInProductStore(): void
    {
        $this->saveProductAttributeValue(
            'simple',
            'test_custom_source_attr',
            (string)\MageSuite\GoogleStructuredData\Test\Integration\Stub\CustomSourceModel::OPTION_ID
        );
        $storeManager = $this->objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $storeId = (int)$storeManager->getStore('default')->getId();
        $product = $this->productRepository->get('simple', false, $storeId, true);
        $storeManager->setCurrentStore(\Magento\Store\Model\Store::ADMIN_CODE);
        $getAttributeValue = $this->objectManager->create(\MageSuite\GoogleStructuredData\Model\Eav\GetAttributeValue::class);

        $this->assertSame(
            \MageSuite\GoogleStructuredData\Test\Integration\Stub\CustomSourceModel::getOptionLabel($storeId),
            $getAttributeValue->execute($product, 'test_custom_source_attr')
        );
    }

    #[\Magento\TestFramework\Fixture\DataFixture('MageSuite_GoogleStructuredData::Test/Integration/_files/test_custom_source_attr.php')]
    #[\Magento\TestFramework\Fixture\DataFixture('MageSuite_GoogleStructuredData::Test/Integration/_files/products_simple.php')]
    public function testItKeepsCustomSourceLabelsSeparatePerStore(): void
    {
        $this->saveProductAttributeValue(
            'simple',
            'test_custom_source_attr',
            (string)\MageSuite\GoogleStructuredData\Test\Integration\Stub\CustomSourceModel::OPTION_ID
        );
        $defaultStoreId = (int)$this->objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore('default')->getId();
        $adminStoreId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        $getAttributeValue = $this->objectManager->create(\MageSuite\GoogleStructuredData\Model\Eav\GetAttributeValue::class);

        $this->assertSame(
            [
                \MageSuite\GoogleStructuredData\Test\Integration\Stub\CustomSourceModel::getOptionLabel($defaultStoreId),
                \MageSuite\GoogleStructuredData\Test\Integration\Stub\CustomSourceModel::getOptionLabel($adminStoreId),
            ],
            [
                $getAttributeValue->execute($this->productRepository->get('simple', false, $defaultStoreId, true), 'test_custom_source_attr'),
                $getAttributeValue->execute($this->productRepository->get('simple', false, $adminStoreId, true), 'test_custom_source_attr'),
            ]
        );
    }

    #[\Magento\TestFramework\Fixture\DataFixture('MageSuite_GoogleStructuredData::Test/Integration/_files/test_custom_source_attr.php')]
    #[\Magento\TestFramework\Fixture\DataFixture('MageSuite_GoogleStructuredData::Test/Integration/_files/products_simple.php')]
    public function testItReturnsNullForCustomSourceAttributeWithoutValue(): void
    {
        $product = $this->productRepository->get('simple', false, null, true);
        $getAttributeValue = $this->objectManager->create(\MageSuite\GoogleStructuredData\Model\Eav\GetAttributeValue::class);

        $this->assertNull($getAttributeValue->execute($product, 'test_custom_source_attr'));
    }

    #[\Magento\TestFramework\Fixture\DataFixture('MageSuite_GoogleStructuredData::Test/Integration/_files/test_custom_source_attr.php')]
    #[\Magento\TestFramework\Fixture\DataFixture('MageSuite_GoogleStructuredData::Test/Integration/_files/products_simple.php')]
    public function testItReturnsNullForStaleCustomSourceOptionIdMatchingOptionIndex(): void
    {
        $product = $this->saveProductAttributeValue('simple', 'test_custom_source_attr', '1');
        $getAttributeValue = $this->objectManager->create(\MageSuite\GoogleStructuredData\Model\Eav\GetAttributeValue::class);

        $this->assertNull($getAttributeValue->execute($product, 'test_custom_source_attr'));
    }

    #[\Magento\TestFramework\Fixture\DataFixture('MageSuite_GoogleStructuredData::Test/Integration/_files/test_age_attr.php')]
    #[\Magento\TestFramework\Fixture\DataFixture('MageSuite_GoogleStructuredData::Test/Integration/_files/products_simple.php')]
    public function testItReturnsLabelOfSelectAttributeWithTableOptions(): void
    {
        $product = $this->saveProductAttributeValue('simple', 'test_age_attr', $this->getOptionIdByLabel('test_age_attr', '3-6'));
        $getAttributeValue = $this->objectManager->create(\MageSuite\GoogleStructuredData\Model\Eav\GetAttributeValue::class);

        $this->assertSame('3-6', $getAttributeValue->execute($product, 'test_age_attr'));
    }

    #[\Magento\TestFramework\Fixture\DataFixture('MageSuite_GoogleStructuredData::Test/Integration/_files/test_age_attr.php')]
    #[\Magento\TestFramework\Fixture\DataFixture('MageSuite_GoogleStructuredData::Test/Integration/_files/products_simple.php')]
    public function testItResolvesTableOptionsThroughBatchLookup(): void
    {
        $product = $this->saveProductAttributeValue('simple', 'test_age_attr', $this->getOptionIdByLabel('test_age_attr', '3-6'));
        $batchAttributeOptionData = $this->createMock(\MageSuite\GoogleStructuredData\Model\Eav\BatchAttributeOptionData::class);
        $batchAttributeOptionData->expects($this->once())
            ->method('getOptionText')
            ->with('test_age_attr', (int)$product->getStoreId(), $product->getData('test_age_attr'))
            ->willReturn('Batch label');
        $getAttributeValue = $this->objectManager->create(
            \MageSuite\GoogleStructuredData\Model\Eav\GetAttributeValue::class,
            ['batchAttributeOptionData' => $batchAttributeOptionData]
        );

        $this->assertSame('Batch label', $getAttributeValue->execute($product, 'test_age_attr'));
    }

    protected function saveProductAttributeValue(
        string $sku,
        string $attributeCode,
        string $value
    ): \Magento\Catalog\Api\Data\ProductInterface {
        $product = $this->productRepository->get($sku);
        $product->setCustomAttribute($attributeCode, $value);
        $this->productRepository->save($product);

        return $this->productRepository->get($sku, false, null, true);
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
