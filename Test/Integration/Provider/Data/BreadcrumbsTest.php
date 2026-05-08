<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Test\Integration\Provider\Data;

/**
 * @magentoAppIsolation enabled
 */
class BreadcrumbsTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\MageSuite\GoogleStructuredData\Provider\Data\Breadcrumbs $breadcrumbDataProvider;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->breadcrumbDataProvider = $this->objectManager->get(\MageSuite\GoogleStructuredData\Provider\Data\Breadcrumbs::class);
    }

    public function testItExcludesHomepageByDefault(): void
    {
        $breadcrumbData = $this->breadcrumbDataProvider->getBreadcrumbsData($this->getBreadcrumbs());

        $this->assertEquals(3, count($breadcrumbData['itemListElement']));
        $this->assertEquals('Women', $breadcrumbData['itemListElement'][0]['item']['name']);
    }

    /**
     * @magentoConfigFixture current_store structured_data/breadcrumbs/include_homepage 1
     */
    public function testItIncludesHomepageWhenConfigured(): void
    {
        $breadcrumbData = $this->breadcrumbDataProvider->getBreadcrumbsData($this->getBreadcrumbs());

        $this->assertEquals(4, count($breadcrumbData['itemListElement']));
        $this->assertEquals('Home', $breadcrumbData['itemListElement'][0]['item']['name']);
        $this->assertEquals(1, $breadcrumbData['itemListElement'][0]['position']);
    }

    protected function getBreadcrumbs(): array
    {
        return [
            [
                'label' => 'Home',
                'link' => 'http://localhost/index.php',
                'first' => true
            ],
            [
                'label' => 'Women',
                'link' => 'http://localhost/index.php/women.html'
            ],
            [
                'label' => 'Shirts',
                'link' => 'http://localhost/index.php/women/shirts.html'
            ],
            [
                'label' => 'Long',
                'link' => 'http://localhost/index.php/women/shirts/long.html'
            ]
        ];
    }
}
