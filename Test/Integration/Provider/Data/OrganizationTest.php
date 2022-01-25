<?php

namespace MageSuite\GoogleStructuredData\Test\Integration\Provider\Data;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class OrganizationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageSuite\GoogleStructuredData\Provider\Data\Organization
     */
    protected $organizationDataProvider;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->organizationDataProvider = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\MageSuite\GoogleStructuredData\Provider\Data\Organization::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/structured_data/organization/logo testlogo.png
     */
    public function testItReturnOrganizationDataCorrectly()
    {
        $organizationData = $this->organizationDataProvider->getOrganizationData();

        $this->assertEquals('Organization', $organizationData['@type']);
        $this->assertEquals('Default Store View', $organizationData['name']);
        $this->assertEquals('http://localhost/index.php/', $organizationData['url']);
    }
}
