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

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->socialDataProvider = $this->objectManager->get(\MageSuite\GoogleStructuredData\Provider\Data\Social::class);
        $this->registry = $this->objectManager->get(\Magento\Framework\Registry::class);
        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store structured_data/social/facebook facebook
     * @magentoConfigFixture current_store structured_data/social/twitter twitter
     * @magentoConfigFixture current_store structured_data/social/google_plus google plus
     * @magentoConfigFixture current_store structured_data/social/instagram instagram
     * @magentoConfigFixture current_store structured_data/social/youtube youtube
     */
    public function testItReturnSocialDataCorrectly()
    {
        $socialData = $this->socialDataProvider->getSocialData();

        $this->assertEquals($this->expectedData(), $socialData);
    }

    protected function expectedData()
    {
        return [
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
    }
}
