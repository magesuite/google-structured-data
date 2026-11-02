<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Test\Unit\Provider\Data\Product;

class CompositeAttributeTest extends \PHPUnit\Framework\TestCase
{
    public function testItSkipsMissingPropertyAttributes(): void
    {
        $additionalPropertyPool = $this->createMock(\MageSuite\GoogleStructuredData\Provider\Data\Product\AdditionalPropertyPool::class);
        $additionalPropertyPool->method('getAttributeCodes')->willReturn(['existing_attribute', 'missing_attribute']);
        $eavConfig = $this->createMock(\Magento\Eav\Model\Config::class);
        $eavConfig->method('getAttribute')->willReturnCallback(fn (string $entityType, string $attributeCode): \Magento\Catalog\Model\ResourceModel\Eav\Attribute => $this->createAttribute($attributeCode === 'existing_attribute' ? 1 : null));

        $compositeAttribute = new \MageSuite\GoogleStructuredData\Provider\Data\Product\CompositeAttribute(
            $this->createMock(\Magento\Framework\Escaper::class),
            $eavConfig,
            $this->createMock(\MageSuite\GoogleStructuredData\Helper\Configuration\Product::class),
            $additionalPropertyPool
        );

        $this->assertSame(['existing_attribute'], $compositeAttribute->getAdditionalPropertyAttributeCodes());
    }

    protected function createAttribute(?int $attributeId): \Magento\Catalog\Model\ResourceModel\Eav\Attribute
    {
        $attribute = $this->createMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $attribute->method('getId')->willReturn($attributeId);

        return $attribute;
    }
}
