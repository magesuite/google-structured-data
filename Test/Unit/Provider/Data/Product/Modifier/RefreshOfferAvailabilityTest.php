<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Test\Unit\Provider\Data\Product\Modifier;

class RefreshOfferAvailabilityTest extends \PHPUnit\Framework\TestCase
{
    protected const IN_STOCK = 'https://schema.org/InStock';
    protected const OUT_OF_STOCK = 'https://schema.org/OutOfStock';

    public function testItRefreshesNestedOfferAvailability(): void
    {
        $productData = [
            [
                '@type' => 'Product',
                'sku' => 'simple-a',
                'offers' => $this->createOffer('simple-a', self::IN_STOCK)
            ],
            [
                '@type' => 'Product',
                'sku' => 'simple-b',
                'offers' => [
                    '@type' => 'AggregateOffer',
                    'offers' => [$this->createOffer('simple-b', self::OUT_OF_STOCK)]
                ]
            ],
            [
                '@type' => 'ProductGroup',
                'hasVariant' => [
                    [
                        '@type' => 'Product',
                        'sku' => 'variant&amp;c',
                        'offers' => $this->createOffer('variant&amp;c', self::IN_STOCK)
                    ]
                ]
            ]
        ];
        $salableStatuses = [
            'simple-a' => false,
            'simple-b' => true,
            'variant&c' => false
        ];

        $result = $this->createModifier($salableStatuses)->execute($productData, $this->createMock(\Magento\Catalog\Api\Data\ProductInterface::class), $this->createStore());

        $this->assertSame(self::OUT_OF_STOCK, $result[0]['offers']['availability']);
        $this->assertSame(self::IN_STOCK, $result[1]['offers']['offers'][0]['availability']);
        $this->assertSame(self::OUT_OF_STOCK, $result[2]['hasVariant'][0]['offers']['availability']);
    }

    public function testItKeepsOffersWithUnknownSku(): void
    {
        $productData = [
            '@type' => 'Product',
            'sku' => 'simple-a',
            'offers' => [
                $this->createOffer('simple-a', self::OUT_OF_STOCK),
                $this->createOffer('not-indexed-sku', self::OUT_OF_STOCK)
            ]
        ];

        $result = $this->createModifier(['simple-a' => true])->execute($productData, $this->createMock(\Magento\Catalog\Api\Data\ProductInterface::class), $this->createStore());

        $this->assertSame(self::IN_STOCK, $result['offers'][0]['availability']);
        $this->assertSame(self::OUT_OF_STOCK, $result['offers'][1]['availability']);
        $this->assertArrayNotHasKey('availability', $result);
    }

    public function testItSkipsRefreshWhenDisabled(): void
    {
        $productData = $this->createOffer('simple-a', self::IN_STOCK);
        $inventoryData = $this->createMock(\MageSuite\GoogleStructuredData\Model\ResourceModel\Product\InventoryData::class);
        $inventoryData->expects($this->never())->method('getSalableStatusesBySku');
        $modifier = new \MageSuite\GoogleStructuredData\Provider\Data\Product\Modifier\RefreshOfferAvailability($inventoryData, $this->createProductConfiguration(false));

        $this->assertSame($productData, $modifier->execute($productData, $this->createMock(\Magento\Catalog\Api\Data\ProductInterface::class), $this->createStore()));
    }

    protected function createOffer(string $sku, string $availability): array
    {
        return [
            '@type' => 'Offer',
            'sku' => $sku,
            'availability' => $availability
        ];
    }

    protected function createModifier(array $salableStatuses): \MageSuite\GoogleStructuredData\Provider\Data\Product\Modifier\RefreshOfferAvailability
    {
        $inventoryData = $this->createMock(\MageSuite\GoogleStructuredData\Model\ResourceModel\Product\InventoryData::class);
        $inventoryData->method('getSalableStatusesBySku')->willReturnCallback(static fn (array $skus): array => array_intersect_key($salableStatuses, array_flip($skus)));

        return new \MageSuite\GoogleStructuredData\Provider\Data\Product\Modifier\RefreshOfferAvailability($inventoryData, $this->createProductConfiguration(true));
    }

    protected function createProductConfiguration(bool $isRefreshEnabled): \MageSuite\GoogleStructuredData\Helper\Configuration\Product
    {
        $productConfiguration = $this->createMock(\MageSuite\GoogleStructuredData\Helper\Configuration\Product::class);
        $productConfiguration->method('isOfferAvailabilityRefreshEnabled')->with(1)->willReturn($isRefreshEnabled);

        return $productConfiguration;
    }

    protected function createStore(): \Magento\Store\Api\Data\StoreInterface
    {
        $store = $this->createMock(\Magento\Store\Api\Data\StoreInterface::class);
        $store->method('getId')->willReturn(1);

        return $store;
    }
}
