<?php
namespace MageSuite\GoogleStructuredData\Test\Unit\Provider\Data;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class SearchBoxTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;
    /**
     * @var \MageSuite\GoogleStructuredData\Provider\Data\SearchBox
     */
    protected $searchBoxDataProvider;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->searchBoxDataProvider = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\MageSuite\GoogleStructuredData\Provider\Data\SearchBox::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testItReturnSearchBoxDataCorrectly()
    {
        $searchBoxData = $this->searchBoxDataProvider->getSearchBoxData();

        $this->assertEquals('WebSite', $searchBoxData['@type']);
        $this->assertEquals('SearchAction', $searchBoxData['potentialAction']['@type']);
        $this->assertEquals('http://localhost/index.php/catalogsearch/result/?q={search_term_string}/', $searchBoxData['potentialAction']['target']);
        $this->assertEquals('required name=search_term_string', $searchBoxData['potentialAction']['query-input']);
    }
}