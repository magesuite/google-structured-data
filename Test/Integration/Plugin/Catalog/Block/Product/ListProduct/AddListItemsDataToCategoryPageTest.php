<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Test\Integration\Plugin\Catalog\Block\Product\ListProduct;

#[\Magento\TestFramework\Fixture\AppIsolation(true)]
#[\Magento\TestFramework\Fixture\DbIsolation(true)]
class AddListItemsDataToCategoryPageTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory;
    protected ?\Magento\Framework\Registry $registry;
    protected ?\MageSuite\GoogleStructuredData\Plugin\Catalog\Block\Product\ListProduct\AddListItemsDataToCategoryPage $plugin;
    protected ?\MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer;
    protected ?\Magento\Framework\App\CacheInterface $cache;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->collectionFactory = $this->objectManager->get(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class);
        $this->registry = $this->objectManager->get(\Magento\Framework\Registry::class);
        $this->plugin = $this->objectManager->get(\MageSuite\GoogleStructuredData\Plugin\Catalog\Block\Product\ListProduct\AddListItemsDataToCategoryPage::class);
        $this->structuredDataContainer = $this->objectManager->get(\MageSuite\GoogleStructuredData\Provider\StructuredDataContainer::class);
        $this->cache = $this->objectManager->get(\Magento\Framework\App\CacheInterface::class);
    }

    protected function tearDown(): void
    {
        $this->cache->clean([\MageSuite\GoogleStructuredData\Provider\Data\Product::CACHE_GROUP]);
        $this->registry->unregister('current_category');
    }

    /**
     * @magentoDataFixture MageSuite_GoogleStructuredData::Test/Integration/_files/products_simple.php
     * @magentoConfigFixture current_store structured_data/category_page/include_list_item 2
     * @magentoConfigFixture current_store structured_data/product_page/is_indexing_enabled 0
     */
    public function testItemListContainsPositionNameAndUrlInProductUrlsMode(): void
    {
        $itemList = $this->buildItemListForSku('simple');

        $this->assertEquals('ItemList', $itemList['@type']);
        $this->assertArrayHasKey('@id', $itemList);
        $this->assertStringEndsWith('#itemlist', $itemList['@id']);
        $this->assertArrayHasKey('name', $itemList);
        $this->assertEquals(1, $itemList['numberOfItems']);
        $this->assertEquals('https://schema.org/ItemListOrderAscending', $itemList['itemListOrder']);
        $this->assertCount(1, $itemList['itemListElement']);

        $listItem = reset($itemList['itemListElement']);
        $this->assertEquals('ListItem', $listItem['@type']);
        $this->assertEquals(1, $listItem['position']);
        $this->assertEquals('Simple Product', $listItem['name']);
        $this->assertArrayHasKey('url', $listItem);
        $this->assertNotEmpty($listItem['url']);
        $this->assertArrayNotHasKey('item', $listItem);
    }

    #[\Magento\TestFramework\Fixture\DataFixture('MageSuite_GoogleStructuredData::Test/Integration/_files/products_simple.php')]
    #[\Magento\TestFramework\Fixture\Config('structured_data/category_page/include_list_item', '2', 'store', 'default')]
    #[\Magento\TestFramework\Fixture\Config('structured_data/category_page/is_collection_page_enabled', '1', 'store', 'default')]
    #[\Magento\TestFramework\Fixture\Config('structured_data/product_page/is_indexing_enabled', '0', 'store', 'default')]
    public function testCollectionPageIsAddedAsSeparateNodeReferencingItemListWhenEnabled(): void
    {
        $structuredData = $this->buildStructuredDataForSku('simple');

        $this->assertArrayHasKey('collection_page', $structuredData);

        $collectionPage = $structuredData['collection_page'];
        $itemList = $structuredData['item_list'];

        $this->assertEquals('CollectionPage', $collectionPage['@type']);
        $this->assertStringEndsWith('#collectionpage', $collectionPage['@id']);
        $this->assertArrayHasKey('name', $collectionPage);
        $this->assertArrayNotHasKey('itemListElement', $collectionPage);
        $this->assertArrayNotHasKey('numberOfItems', $collectionPage);
        $this->assertArrayNotHasKey('itemListOrder', $collectionPage);

        $this->assertStringEndsWith('#website', $collectionPage['isPartOf']['@id']);
        $this->assertStringEndsWith('#breadcrumb', $collectionPage['breadcrumb']['@id']);
        $this->assertEquals($itemList['@id'], $collectionPage['mainEntity']['@id']);

        $this->assertEquals('ItemList', $itemList['@type']);
        $this->assertCount(1, $itemList['itemListElement']);

        $listItem = reset($itemList['itemListElement']);
        $this->assertEquals('ListItem', $listItem['@type']);
        $this->assertEquals(1, $listItem['position']);
        $this->assertEquals('Simple Product', $listItem['name']);
        $this->assertNotEmpty($listItem['url']);
        $this->assertArrayNotHasKey('item', $listItem);
    }

    #[\Magento\TestFramework\Fixture\DataFixture('MageSuite_GoogleStructuredData::Test/Integration/_files/products_simple.php')]
    #[\Magento\TestFramework\Fixture\Config('structured_data/category_page/include_list_item', '2', 'store', 'default')]
    #[\Magento\TestFramework\Fixture\Config('structured_data/category_page/is_collection_page_enabled', '0', 'store', 'default')]
    #[\Magento\TestFramework\Fixture\Config('structured_data/product_page/is_indexing_enabled', '0', 'store', 'default')]
    public function testCollectionPageIsNotAddedWhenDisabled(): void
    {
        $structuredData = $this->buildStructuredDataForSku('simple');

        $this->assertArrayNotHasKey('collection_page', $structuredData);
        $this->assertEquals('ItemList', $structuredData['item_list']['@type']);
    }

    /**
     * @magentoDataFixture MageSuite_GoogleStructuredData::Test/Integration/_files/products_simple.php
     * @magentoConfigFixture current_store structured_data/category_page/include_list_item 1
     * @magentoConfigFixture current_store structured_data/product_page/is_indexing_enabled 0
     */
    public function testItemListEmbedsFullProductInFullProductDataMode(): void
    {
        $itemList = $this->buildItemListForSku('simple');

        $listItem = reset($itemList['itemListElement']);
        $this->assertArrayHasKey('item', $listItem);
        $this->assertEquals('Product', $listItem['item']['@type']);
    }

    protected function buildItemListForSku(string $sku): array
    {
        return $this->buildStructuredDataForSku($sku)['item_list'];
    }

    protected function buildStructuredDataForSku(string $sku): array
    {
        $category = $this->objectManager->create(\Magento\Catalog\Model\Category::class);
        $category->setId(2);
        $this->registry->register('current_category', $category);

        $collection = $this->collectionFactory->create();
        $collection->addAttributeToFilter('sku', $sku);
        $collection->addAttributeToSelect(['name', 'url_key']);

        $listProductBlock = $this->objectManager->create(\Magento\Catalog\Block\Product\ListProduct::class);
        $this->plugin->afterGetLoadedProductCollection($listProductBlock, $collection);

        return $this->structuredDataContainer->getStructuredData();
    }
}
