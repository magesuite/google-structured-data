<?php

namespace MageSuite\GoogleStructuredData\Test\Integration\Provider\Data;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class SearchBoxTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;

    protected ?\MageSuite\GoogleStructuredData\Provider\Data\SearchBox $searchBoxDataProvider;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->searchBoxDataProvider = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\MageSuite\GoogleStructuredData\Provider\Data\SearchBox::class);
    }

    public function testItReturnSearchBoxDataCorrectly()
    {
        $searchBoxData = $this->searchBoxDataProvider->getSearchBoxData();

        $this->assertEquals('WebSite', $searchBoxData['@type']);
        $this->assertEquals('SearchAction', $searchBoxData['potentialAction']['@type']);
        $this->assertEquals('http://localhost/index.php/catalogsearch/result/?q={search_term_string}/', $searchBoxData['potentialAction']['target']);
        $this->assertEquals('required name=search_term_string', $searchBoxData['potentialAction']['query-input']);
    }
}
