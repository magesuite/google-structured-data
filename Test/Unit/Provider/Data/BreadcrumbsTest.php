<?php
namespace MageSuite\GoogleStructuredData\Test\Unit\Provider;

class BreadcrumbsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;
    /**
     * @var \MageSuite\GoogleStructuredData\Provider\Data\Breadcrumbs
     */
    protected $breadcrumbDataProvider;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->breadcrumbDataProvider = $this->objectManager->get(\MageSuite\GoogleStructuredData\Provider\Data\Breadcrumbs::class);
    }


    public function testItReturnBreadcrumbDataCorrectly()
    {
        $breadcrumbs = $this->getBreadcrumbs();

        $breadcrumbData = $this->breadcrumbDataProvider->getBreadcrumbsData($breadcrumbs);

        $this->assertEquals('BreadcrumbList', $breadcrumbData['@type']);
        $this->assertEquals(4, count($breadcrumbData['itemListElement']));

        foreach ($breadcrumbData['itemListElement'] as $i => $crumb) {
            $this->assertEquals($breadcrumbs[$i]['link'], $crumb['item']['@id']);
            $this->assertEquals($breadcrumbs[$i]['label'], $crumb['item']['name']);
        }
    }

    protected function getBreadcrumbs()
    {
        return [
            [
                'label' => 'Home',
                'link' => 'http://localhost/index.php'
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