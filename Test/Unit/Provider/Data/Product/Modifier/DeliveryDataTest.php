<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Test\Unit\Provider\Data\Product\Modifier;

class DeliveryDataTest extends \PHPUnit\Framework\TestCase
{
    protected \Magento\Store\Model\Store $store;

    protected function setUp(): void
    {
        $website = $this->createMock(\Magento\Store\Api\Data\WebsiteInterface::class);
        $this->store = $this->createMock(\Magento\Store\Model\Store::class);
        $this->store->method('getId')->willReturn(1);
        $this->store->method('getWebsite')->willReturn($website);
    }

    public function testItConvertsCarrierPriceToDisplayCurrency(): void
    {
        $deliveryData = $this->getDeliveryData(
            ['flatrate' => ['active' => '1', 'name' => 'Flat', 'price' => '10.00']],
            'EUR',
            ['EUR' => 0.5]
        );

        $this->assertCount(1, $deliveryData);
        $this->assertSame(5.0, $deliveryData[0]['shippingRate']['value']);
        $this->assertSame('EUR', $deliveryData[0]['shippingRate']['currency']);
    }

    public function testItKeepsCarrierPriceWhenDisplayCurrencyIsBaseCurrency(): void
    {
        $deliveryData = $this->getDeliveryData(
            ['flatrate' => ['active' => '1', 'name' => 'Flat', 'price' => '10.00']],
            'USD',
            []
        );

        $this->assertSame(10.0, $deliveryData[0]['shippingRate']['value']);
        $this->assertSame('USD', $deliveryData[0]['shippingRate']['currency']);
    }

    protected function getDeliveryData(array $carriers, string $currencyCode, array $rates): array
    {
        $configuration = $this->createMock(\MageSuite\GoogleStructuredData\Helper\Configuration::class);
        $configuration->method('getCarriers')->willReturn($carriers);
        $configuration->method('getCountryByWebsite')->willReturn('DE');

        $productConfiguration = $this->createMock(\MageSuite\GoogleStructuredData\Helper\Configuration\Product::class);
        $productConfiguration->method('isDeliveryDataEnabled')->willReturn(true);

        $priceCurrency = $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);
        $priceCurrency->method('convertAndRound')->willReturnCallback(
            static fn ($amount, $scope = null, $currency = null) => round((float)$amount * ($rates[$currency] ?? 1), 2)
        );

        $serializer = $this->createMock(\Magento\Framework\Serialize\SerializerInterface::class);
        $serializer->method('serialize')->willReturnCallback(static fn ($data) => json_encode($data));

        $dataObjectFactory = $this->createMock(\Magento\Framework\DataObjectFactory::class);
        $dataObjectFactory->method('create')->willReturnCallback(static fn () => new \Magento\Framework\DataObject());

        $modifier = new \MageSuite\GoogleStructuredData\Provider\Data\Product\Modifier\DeliveryData(
            new \Magento\Framework\Stdlib\ArrayManager(),
            $dataObjectFactory,
            $serializer,
            $this->createMock(\MageSuite\GoogleStructuredData\Provider\Data\Product\DeliveryData\BusinessDays::class),
            $this->createMock(\MageSuite\GoogleStructuredData\Provider\Data\Product\DeliveryData\CutoffTime::class),
            $this->createMock(\MageSuite\GoogleStructuredData\Provider\Data\Product\DeliveryData\HandlingTime::class),
            $this->createMock(\MageSuite\GoogleStructuredData\Provider\Data\Product\DeliveryData\TransitTime::class),
            $productConfiguration,
            $configuration,
            $priceCurrency
        );

        $dataObject = new \Magento\Framework\DataObject();
        $dataObject->setData('store', $this->store);
        $dataObject->setData('currency_code', $currencyCode);

        return $modifier->getDeliveryData($dataObject);
    }
}
