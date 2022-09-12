<?php

namespace MageSuite\GoogleStructuredData\Test\Integration\Provider\Data;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class OrganizationTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;

    protected ?\MageSuite\GoogleStructuredData\Provider\Data\Organization $organizationDataProvider;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->organizationDataProvider = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\MageSuite\GoogleStructuredData\Provider\Data\Organization::class);
    }

    /**
     * @magentoConfigFixture current_store structured_data/organization/logo testlogo.png
     * @magentoConfigFixture current_store structured_data/organization/address/postal 00000
     * @magentoConfigFixture current_store structured_data/organization/address/city City
     * @magentoConfigFixture current_store structured_data/organization/address/street Street 1
     * @magentoConfigFixture current_store structured_data/organization/address/country DE
     * @magentoConfigFixture current_store structured_data/organization/address/country DE
     * @magentoConfigFixture current_store structured_data/organization/contact/sales_telephone 111222333
     * @magentoConfigFixture current_store structured_data/organization/contact/sales_email test@example.com
     */
    public function testItReturnOrganizationDataCorrectly()
    {
        $expectedData = [
            '@context' => 'http://schema.org',
            '@type' => 'Organization',
            'name' => 'Default Store View',
            'url' => 'http://localhost/index.php/',
            'logo' => 'testlogo.png',
            'address' => [
                '@type' => 'PostalAddress',
                'postalCode' => '00000',
                'addressLocality' => 'City',
                'streetAddress' => 'Street 1',
                'addressCountry' => 'DE',
            ],
            'contactPoint' => [
                [
                    '@type' => 'ContactPoint',
                    'contactType' => 'sales',
                    'telephone' => '111222333',
                    'email' => 'test@example.com',
                ]
            ]
        ];

        $organizationData = $this->organizationDataProvider->getOrganizationData();

        $this->assertEquals($expectedData, $organizationData);
    }
}
