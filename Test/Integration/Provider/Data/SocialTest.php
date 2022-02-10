<?php

namespace MageSuite\GoogleStructuredData\Test\Integration\Provider;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class SocialTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageSuite\GoogleStructuredData\Provider\Data\Social
     */
    protected $socialDataProvider;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->socialDataProvider = $this->objectManager->get(\MageSuite\GoogleStructuredData\Provider\Data\Social::class);
    }

    /**
     * @magentoConfigFixture current_store structured_data/social/profiles/facebook facebook
     * @magentoConfigFixture current_store structured_data/social/profiles/twitter twitter
     * @magentoConfigFixture current_store structured_data/social/profiles/google_plus google plus
     * @magentoConfigFixture current_store structured_data/social/profiles/instagram instagram
     * @magentoConfigFixture current_store structured_data/social/profiles/youtube youtube
     */
    public function testItReturnSocialDataCorrectly()
    {
        $expectedData = [
            '@context' => "http://schema.org",
            '@type' => "Person",
            'name' => "Default Store View",
            'url' => "http://localhost/index.php/",
            'sameAs' => [
                'facebook',
                'twitter',
                'google plus',
                'instagram',
                'youtube'
            ]
        ];

        $socialData = $this->socialDataProvider->getSocialData();

        $this->assertEquals($expectedData, $socialData);
    }
}
