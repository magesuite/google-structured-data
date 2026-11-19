<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Test\Integration\Observer;

#[\Magento\TestFramework\Fixture\AppArea('frontend')]
#[\Magento\TestFramework\Fixture\AppIsolation(true)]
#[\Magento\TestFramework\Fixture\DbIsolation(true)]
class AddCollectionPageDataTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\Magento\Framework\Event\ManagerInterface $eventManager;
    protected ?\Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository;
    protected ?\Magento\Framework\Registry $registry;
    protected ?\MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer;
    protected ?\Magento\TestFramework\Fixture\DataFixtureStorage $fixtures;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->eventManager = $this->objectManager->get(\Magento\Framework\Event\ManagerInterface::class);
        $this->categoryRepository = $this->objectManager->get(\Magento\Catalog\Api\CategoryRepositoryInterface::class);
        $this->registry = $this->objectManager->get(\Magento\Framework\Registry::class);
        $this->structuredDataContainer = $this->objectManager->get(\MageSuite\GoogleStructuredData\Provider\StructuredDataContainer::class);
        $this->fixtures = $this->objectManager->get(\Magento\TestFramework\Fixture\DataFixtureStorageManager::class)->getStorage();
    }

    protected function tearDown(): void
    {
        $this->registry->unregister('current_category');
    }

    #[\Magento\TestFramework\Fixture\DataFixture(
        \Magento\Catalog\Test\Fixture\Category::class,
        ['name' => 'Toys', 'meta_title' => 'Toys meta title', 'meta_description' => 'Toys meta description'],
        as: 'category'
    )]
    #[\Magento\TestFramework\Fixture\Config('structured_data/category_page/is_collection_page_enabled', '1', 'store', 'default')]
    #[\Magento\TestFramework\Fixture\Config('general/locale/code', 'de_DE', 'store', 'default')]
    public function testCollectionPageIsAddedWhenNoProductListIsRendered(): void
    {
        $this->registerCurrentCategory();
        $this->sendResponse();

        $collectionPage = $this->structuredDataContainer->getStructuredData()['collection_page'];

        $this->assertEquals('CollectionPage', $collectionPage['@type']);
        $this->assertEquals($collectionPage['url'] . '#webpage', $collectionPage['@id']);
        $this->assertEquals('Toys meta title', $collectionPage['name']);
        $this->assertEquals('Toys meta description', $collectionPage['description']);
        $this->assertEquals('de-DE', $collectionPage['inLanguage']);
    }

    #[\Magento\TestFramework\Fixture\DataFixture(
        \Magento\Catalog\Test\Fixture\Category::class,
        ['name' => 'Toys'],
        as: 'category'
    )]
    #[\Magento\TestFramework\Fixture\Config('structured_data/category_page/is_collection_page_enabled', '1', 'store', 'default')]
    #[\Magento\TestFramework\Fixture\Config('structured_data/website/is_enabled', '1', 'store', 'default')]
    #[\Magento\TestFramework\Fixture\Config('structured_data/organization/is_enabled', '1', 'store', 'default')]
    #[\Magento\TestFramework\Fixture\Config('structured_data/breadcrumbs/is_enabled', '1', 'store', 'default')]
    public function testReferencesArePointedAtNodesPresentInTheGraphOnly(): void
    {
        $this->registerCurrentCategory();
        $this->eventManager->dispatch('layout_generate_blocks_after');
        $this->sendResponse();

        $structuredData = $this->structuredDataContainer->getStructuredData();
        $collectionPage = $structuredData['collection_page'];

        $this->assertEquals($structuredData['website']['@id'], $collectionPage['isPartOf']['@id']);
        $this->assertEquals($structuredData['organization']['@id'], $collectionPage['publisher']['@id']);
        $this->assertArrayNotHasKey('breadcrumbs', $structuredData);
        $this->assertArrayNotHasKey('breadcrumb', $collectionPage);
        $this->assertArrayNotHasKey('item_list', $structuredData);
        $this->assertArrayNotHasKey('mainEntity', $collectionPage);
    }

    #[\Magento\TestFramework\Fixture\DataFixture(
        \Magento\Catalog\Test\Fixture\Category::class,
        ['name' => 'Toys'],
        as: 'category'
    )]
    #[\Magento\TestFramework\Fixture\Config('structured_data/category_page/is_collection_page_enabled', '1', 'store', 'default')]
    public function testNameFallsBackToCategoryNameAndEmptyFieldsAreOmitted(): void
    {
        $this->registerCurrentCategory();
        $this->sendResponse();

        $collectionPage = $this->structuredDataContainer->getStructuredData()['collection_page'];

        $this->assertEquals('Toys', $collectionPage['name']);
        $this->assertArrayNotHasKey('description', $collectionPage);
    }

    #[\Magento\TestFramework\Fixture\DataFixture(
        \Magento\Catalog\Test\Fixture\Category::class,
        ['name' => 'Toys'],
        as: 'category'
    )]
    #[\Magento\TestFramework\Fixture\Config('structured_data/category_page/is_collection_page_enabled', '0', 'store', 'default')]
    public function testCollectionPageIsNotAddedWhenDisabled(): void
    {
        $this->registerCurrentCategory();
        $this->sendResponse();

        $this->assertArrayNotHasKey('collection_page', $this->structuredDataContainer->getStructuredData());
    }

    #[\Magento\TestFramework\Fixture\DataFixture(
        \Magento\Catalog\Test\Fixture\Category::class,
        ['name' => 'Toys'],
        as: 'category'
    )]
    #[\Magento\TestFramework\Fixture\Config('structured_data/category_page/is_collection_page_enabled', '1', 'store', 'default')]
    public function testCollectionPageIsNotAddedOnAProductPageWithCategoryContext(): void
    {
        $this->registerCurrentCategory();
        $this->sendResponse('product');

        $this->assertArrayNotHasKey('collection_page', $this->structuredDataContainer->getStructuredData());
    }

    protected function registerCurrentCategory(): void
    {
        $category = $this->categoryRepository->get((int)$this->fixtures->get('category')->getId());
        $this->registry->register('current_category', $category);
    }

    protected function sendResponse(string $controllerName = 'category'): void
    {
        $request = $this->objectManager->get(\Magento\Framework\App\RequestInterface::class);
        $request->setRouteName('catalog');
        $request->setControllerName($controllerName);
        $request->setActionName('view');

        $this->eventManager->dispatch(
            'controller_front_send_response_before',
            [
                'request' => $request,
                'response' => $this->objectManager->create(\Magento\Framework\App\ResponseInterface::class)
            ]
        );
    }
}
