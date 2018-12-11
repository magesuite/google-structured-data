<?php
namespace MageSuite\GoogleStructuredData\Test\Unit\Provider\Data;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class SearchBoxTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \MageSuite\GoogleStructuredData\Provider\Data\SearchBox
     */
    protected $searchBoxDataProvider;

    protected function setUp()
    {
        $this->searchBoxDataProvider = Bootstrap::getObjectManager()->get(\MageSuite\GoogleStructuredData\Provider\Data\SearchBox::class);
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
        $this->assertEquals('http://localhost/index.php/catalogsearch/result/?q={search_term_string}', $searchBoxData['potentialAction']['target']);
        $this->assertEquals('required name=search_term_string', $searchBoxData['potentialAction']['query-input']);
    }
}