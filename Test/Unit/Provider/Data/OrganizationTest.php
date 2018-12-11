<?php
namespace MageSuite\GoogleStructuredData\Test\Unit\Provider\Data;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class OrganizationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \MageSuite\GoogleStructuredData\Provider\Data\Organization
     */
    protected $organizationDataProvider;

    protected function setUp()
    {
        $this->organizationDataProvider = Bootstrap::getObjectManager()->get(\MageSuite\GoogleStructuredData\Provider\Data\Organization::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testItReturnOrganizationDataCorrectly()
    {
        $organizationData = $this->organizationDataProvider->getOrganizationData();

        $this->assertEquals('Organization', $organizationData['@type']);
        $this->assertEquals('Default Store View', $organizationData['name']);
        $this->assertEquals('http://localhost/index.php/', $organizationData['url']);
    }
}