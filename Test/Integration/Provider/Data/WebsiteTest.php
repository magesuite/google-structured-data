<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Test\Integration\Provider\Data;

#[\Magento\TestFramework\Fixture\AppIsolation(true)]
#[\Magento\TestFramework\Fixture\DbIsolation(true)]
class WebsiteTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\MageSuite\GoogleStructuredData\Provider\Data\Website $websiteDataProvider;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->websiteDataProvider = $this->objectManager->get(\MageSuite\GoogleStructuredData\Provider\Data\Website::class);
    }

    /**
     * @magentoConfigFixture current_store general/locale/code de_DE
     */
    public function testItReturnsWebsiteDataWithBaseUrlIdByDefault(): void
    {
        $websiteData = $this->websiteDataProvider->getWebsiteData();

        $this->assertEquals('WebSite', $websiteData['@type']);
        $this->assertEquals('http://localhost/index.php/#website', $websiteData['@id']);
        $this->assertEquals('http://localhost/index.php/', $websiteData['url']);
        $this->assertEquals('de-DE', $websiteData['inLanguage']);
        $this->assertEquals('http://localhost/index.php/#organization', $websiteData['publisher']['@id']);
        $this->assertArrayNotHasKey('potentialAction', $websiteData);
    }

    /**
     * @magentoConfigFixture current_store structured_data/website/name Pack2Go
     */
    public function testItUsesConfiguredWebsiteName(): void
    {
        $websiteData = $this->websiteDataProvider->getWebsiteData();

        $this->assertEquals('Pack2Go', $websiteData['name']);
    }

    /**
     * @magentoConfigFixture current_store structured_data/general/id_use_root_domain 1
     */
    public function testItUsesRootDomainForIdWhenEnabled(): void
    {
        $websiteData = $this->websiteDataProvider->getWebsiteData();

        $this->assertEquals('http://localhost/#website', $websiteData['@id']);
        $this->assertEquals('http://localhost/#organization', $websiteData['publisher']['@id']);
    }
}
