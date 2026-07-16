<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Test\Integration\Provider\Data;

#[\Magento\TestFramework\Fixture\AppIsolation(true)]
#[\Magento\TestFramework\Fixture\DbIsolation(true)]
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
     * @magentoConfigFixture current_store structured_data/organization/description Toys for Fun ist ein Onlineshop.
     * @magentoConfigFixture current_store structured_data/organization/alternate_name Toys for Fun
     * @magentoConfigFixture current_store structured_data/organization/founding_date 2006
     * @magentoConfigFixture current_store structured_data/organization/telephone 111222333
     * @magentoConfigFixture current_store structured_data/organization/email admin@example.com
     * @magentoConfigFixture current_store structured_data/organization/address/postal 00000
     * @magentoConfigFixture current_store structured_data/organization/address/city City
     * @magentoConfigFixture current_store structured_data/organization/address/street Street 1
     * @magentoConfigFixture current_store structured_data/organization/address/country DE
     * @magentoConfigFixture current_store structured_data/organization/address/country DE
     * @magentoConfigFixture current_store structured_data/organization/contact/sales_telephone 111222333
     * @magentoConfigFixture current_store structured_data/organization/contact/sales_email test@example.com
     * @magentoConfigFixture current_store structured_data/organization/return_policy/is_enabled 1
     * @magentoConfigFixture current_store structured_data/organization/return_policy/return_policy_category MerchantReturnFiniteReturnWindow
     * @magentoConfigFixture current_store structured_data/organization/return_policy/return_days 7
     * @magentoConfigFixture current_store structured_data/organization/return_policy/return_method ReturnByMail
     * @magentoConfigFixture current_store structured_data/organization/return_policy/return_fees FreeReturn
     * @magentoConfigFixture current_store structured_data/organization/return_policy/refund_type FullRefund
     */
    public function testItReturnOrganizationDataCorrectly(): void
    {
        $expectedData = [
            '@type' => 'Organization',
            '@id' => 'http://localhost/index.php/#organization',
            'name' => 'Default Store View',
            'url' => 'http://localhost/index.php/',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => 'testlogo.png'
            ],
            'description' => 'Toys for Fun ist ein Onlineshop.',
            'alternateName' => ['Toys for Fun'],
            'foundingDate' => '2006',
            'telephone' => '111222333',
            'email' => 'admin@example.com',
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
            ],
            'hasMerchantReturnPolicy' => [
                '@type' => 'MerchantReturnPolicy',
                'applicableCountry' => 'US',
                'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
                'merchantReturnDays' => 7,
                'returnMethod' => 'https://schema.org/ReturnByMail',
                'returnFees' => 'https://schema.org/FreeReturn',
                'refundType' => 'https://schema.org/FullRefund'
            ]
        ];

        $organizationData = $this->organizationDataProvider->getOrganizationData();

        $this->assertEquals($expectedData, $organizationData);
    }

    /**
     * @magentoConfigFixture current_store structured_data/organization/contact/technical_telephone 111
     * @magentoConfigFixture current_store structured_data/organization/contact/technical_email tech@example.com
     * @magentoConfigFixture current_store structured_data/organization/contact/customer_service_telephone 222
     * @magentoConfigFixture current_store structured_data/organization/contact/customer_service_email service@example.com
     */
    public function testContactPointUsesProperContactType(): void
    {
        $organizationData = $this->organizationDataProvider->getOrganizationData();

        $contactTypes = array_column($organizationData['contactPoint'], 'contactType');

        $this->assertContains('technical support', $contactTypes);
        $this->assertContains('customer service', $contactTypes);
        $this->assertNotContains('sales', $contactTypes);
    }

    /**
     * @magentoConfigFixture current_store structured_data/organization/social/is_enabled 1
     * @magentoConfigFixture current_store structured_data/organization/social/profiles/facebook https://facebook.com/example
     * @magentoConfigFixture current_store structured_data/organization/social/profiles/instagram https://instagram.com/example
     */
    public function testItIncludesSameAsWhenSocialProfilesConfigured(): void
    {
        $organizationData = $this->organizationDataProvider->getOrganizationData();

        $this->assertArrayHasKey('sameAs', $organizationData);
        $this->assertContains('https://facebook.com/example', $organizationData['sameAs']);
        $this->assertContains('https://instagram.com/example', $organizationData['sameAs']);
    }

    /**
     * @magentoConfigFixture current_store structured_data/organization/social/is_enabled 0
     * @magentoConfigFixture current_store structured_data/organization/social/profiles/facebook https://facebook.com/example
     */
    public function testItExcludesSameAsWhenSocialProfilesDisabled(): void
    {
        $organizationData = $this->organizationDataProvider->getOrganizationData();

        $this->assertArrayNotHasKey('sameAs', $organizationData);
    }
}
