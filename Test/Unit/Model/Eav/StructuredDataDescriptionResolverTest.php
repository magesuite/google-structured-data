<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Test\Unit\Model\Eav;

class StructuredDataDescriptionResolverTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('getDescriptionCases')]
    public function testItResolvesDescriptionForStoreView(string $description, ?string $expectedDescription): void
    {
        $resolver = $this->createResolver($this->createMock(\Psr\Log\LoggerInterface::class));

        $this->assertSame($expectedDescription, $resolver->resolve($this->createAttribute($description), 2));
    }

    public static function getDescriptionCases(): array
    {
        return [
            'entry_of_store_view' => ['{"default": "Default context", "second": " Second context "}', 'Second context'],
            'entry_of_default_store_view_when_store_view_has_none' => ['{"default": "Default context"}', 'Default context'],
            'plain_text_for_every_store_view' => [' Plain context ', 'Plain context'],
            'nothing_for_malformed_json' => ['{"default": "Default context"', null]
        ];
    }

    public function testItLogsMalformedDescription(): void
    {
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $logger->expects($this->once())->method('warning')->with($this->stringContains('"test_attribute"'));

        $this->createResolver($logger)->resolve($this->createAttribute('{"default": '), 2);
    }

    protected function createResolver(\Psr\Log\LoggerInterface $logger): \MageSuite\GoogleStructuredData\Model\Eav\StructuredDataDescriptionResolver
    {
        return new \MageSuite\GoogleStructuredData\Model\Eav\StructuredDataDescriptionResolver(new \Magento\Framework\Serialize\Serializer\Json(), $this->createStoreManager(), $logger);
    }

    protected function createAttribute(string $description): \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
    {
        $attribute = $this->createMock(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class);
        $attribute->method('getAttributeCode')->willReturn('test_attribute');
        $attribute->method('getData')->with('structured_data_description')->willReturn($description);

        return $attribute;
    }

    protected function createStoreManager(): \Magento\Store\Model\StoreManagerInterface
    {
        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $store->method('getCode')->willReturn('second');
        $store->method('getWebsiteId')->willReturn(1);

        $defaultStore = $this->createMock(\Magento\Store\Model\Store::class);
        $defaultStore->method('getCode')->willReturn('default');

        $website = $this->createMock(\Magento\Store\Model\Website::class);
        $website->method('getDefaultStore')->willReturn($defaultStore);

        $storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $storeManager->method('getStore')->with(2)->willReturn($store);
        $storeManager->method('getWebsite')->with(1)->willReturn($website);

        return $storeManager;
    }
}
