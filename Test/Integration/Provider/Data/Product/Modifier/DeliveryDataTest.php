<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Test\Integration\Provider\Data\Product\Modifier;

class DeliveryDataTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\Magento\Store\Model\StoreManagerInterface $storeManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->storeManager = $this->objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
    }

    #[\Magento\TestFramework\Fixture\Config('structured_data/product_page/delivery_data/is_enabled', '1', 'store', 'default')]
    public function testItReturnsSingleShippingDetailsForCarriersWithEqualRates(): void
    {
        $deliveryData = $this->getDeliveryData([
            'first_carrier' => ['active' => '1', 'name' => 'First', 'price' => '0.00'],
            'second_carrier' => ['active' => '1', 'name' => 'Second', 'price' => '0']
        ]);

        $this->assertCount(1, $deliveryData);
        $this->assertSame(0.0, $deliveryData[0]['shippingRate']['value']);
    }

    #[\Magento\TestFramework\Fixture\Config('structured_data/product_page/delivery_data/is_enabled', '1', 'store', 'default')]
    public function testItReturnsSeparateShippingDetailsForCarriersWithDifferentRates(): void
    {
        $deliveryData = $this->getDeliveryData([
            'first_carrier' => ['active' => '1', 'name' => 'First', 'price' => '0.00'],
            'second_carrier' => ['active' => '1', 'name' => 'Second', 'price' => '15.90']
        ]);

        $this->assertCount(2, $deliveryData);
        $this->assertSame([0.0, 15.90], array_column(array_column($deliveryData, 'shippingRate'), 'value'));
    }

    protected function getDeliveryData(array $carriers): array
    {
        $store = $this->storeManager->getStore();

        $configuration = $this->createMock(\MageSuite\GoogleStructuredData\Helper\Configuration::class);
        $configuration->method('getCarriers')->willReturn($carriers);
        $configuration->method('getCountryByWebsite')->willReturn('DE');

        $modifier = $this->objectManager->create(
            \MageSuite\GoogleStructuredData\Provider\Data\Product\Modifier\DeliveryData::class,
            ['configuration' => $configuration]
        );

        $dataObject = $this->objectManager->create(\Magento\Framework\DataObject::class);
        $dataObject->setData('store', $store);
        $dataObject->setData('currency_code', $store->getCurrentCurrencyCode());

        return $modifier->getDeliveryData($dataObject);
    }
}
