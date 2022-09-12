<?php

namespace MageSuite\GoogleStructuredData\Test\Unit\Provider;

class BreadcrumbsTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;

    protected ?\MageSuite\GoogleStructuredData\Provider\Data\Breadcrumbs $breadcrumbDataProvider;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->breadcrumbDataProvider = $this->objectManager->get(\MageSuite\GoogleStructuredData\Provider\Data\Breadcrumbs::class);
    }

    public function testItReturnBreadcrumbDataCorrectly()
    {
        $breadcrumbs = $this->getBreadcrumbs();

        $breadcrumbData = $this->breadcrumbDataProvider->getBreadcrumbsData($breadcrumbs);

        $this->assertEquals('BreadcrumbList', $breadcrumbData['@type']);
        $this->assertEquals(3, count($breadcrumbData['itemListElement']));

        foreach ($breadcrumbData['itemListElement'] as $index => $crumb) {
            $this->assertEquals($breadcrumbs[$index + 1]['link'], $crumb['item']['@id']);
            $this->assertEquals($breadcrumbs[$index + 1]['label'], $crumb['item']['name']);
        }
    }

    protected function getBreadcrumbs()
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
