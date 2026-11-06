<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Test\Integration\Model\Indexer\Product;

#[\Magento\TestFramework\Fixture\DbIsolation(true)]
#[\Magento\TestFramework\Fixture\AppIsolation(true)]
class IndexDataGeneratorTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\Magento\Store\Model\StoreManagerInterface $storeManager;
    protected ?\Magento\Framework\Serialize\SerializerInterface $serializer;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->storeManager = $this->objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $this->serializer = $this->objectManager->get(\Magento\Framework\Serialize\SerializerInterface::class);
    }

    #[\Magento\TestFramework\Fixture\DataFixture('MageSuite_GoogleStructuredData::Test/Integration/_files/products_simple.php')]
    #[\Magento\TestFramework\Fixture\DataFixture(\Magento\CatalogRule\Test\Fixture\Rule::class, ['discount_amount' => 10])]
    public function testCatalogRulePriceIsAppliedWhenGeneratedInAdminContext(): void
    {
        $targetStore = $this->storeManager->getStore('default');
        $productId = (int)$this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class)->get('simple')->getId();
        $this->objectManager->get(\Magento\CatalogRule\Model\Indexer\IndexBuilder::class)->reindexByIds([$productId]);
        $this->storeManager->setCurrentStore(\Magento\Store\Model\Store::ADMIN_CODE);

        $generatedData = $this->generateIndexData((int)$targetStore->getId(), $productId);
        $productData = $this->serializer->unserialize($generatedData[0]['data']);

        $this->assertSame('9.00', $productData['offers']['price']);
        $this->assertSame(\Magento\Store\Model\Store::ADMIN_CODE, $this->storeManager->getStore()->getCode());
    }

    protected function generateIndexData(int $storeId, int $productId): array
    {
        $dimension = $this->objectManager->create(\Magento\Framework\Indexer\Dimension::class, [
            'name' => \Magento\Store\Model\StoreDimensionProvider::DIMENSION_NAME,
            'value' => (string)$storeId
        ]);
        $products = $this->objectManager->create(\MageSuite\GoogleStructuredData\Model\Indexer\Product\DataProvider::class)->getProducts(
            [\Magento\Store\Model\StoreDimensionProvider::DIMENSION_NAME => $dimension],
            [$productId]
        );

        return $this->objectManager->create(\MageSuite\GoogleStructuredData\Model\Indexer\Product\IndexDataGenerator::class)->generate($storeId, $products);
    }
}
