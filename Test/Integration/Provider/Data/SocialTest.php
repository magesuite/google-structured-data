<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Test\Integration\Provider\Data;

use Magento\TestFramework\Fixture\Config; // phpcs:ignore

class SocialTest extends \PHPUnit\Framework\TestCase
{
    protected ?\MageSuite\GoogleStructuredData\Provider\Data\Social $socialDataProvider = null;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->socialDataProvider = $objectManager->get(\MageSuite\GoogleStructuredData\Provider\Data\Social::class);
    }

    #[Config('structured_data/social/profiles/facebook', 'facebook', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 'default')]
    #[Config('structured_data/social/profiles/twitter', 'twitter', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 'default')]
    #[Config('structured_data/social/profiles/google_plus', 'google plus', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 'default')]
    #[Config('structured_data/social/profiles/instagram', 'instagram', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 'default')]
    #[Config('structured_data/social/profiles/youtube', 'youtube', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 'default')]
    #[Config('structured_data/social/profiles/tiktok', 'tiktok', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 'default')]
    public function testItReturnSocialDataCorrectly(): void
    {
        $expectedData = [
            '@context' => "https://schema.org",
            '@type' => "Person",
            'name' => "Default Store View",
            'url' => "http://localhost/index.php/",
            'sameAs' => [
                'facebook',
                'twitter',
                'google plus',
                'instagram',
                'youtube',
                'tiktok'
            ]
        ];

        $socialData = $this->socialDataProvider->getSocialData();

        $this->assertEquals($expectedData, $socialData);
    }
}
