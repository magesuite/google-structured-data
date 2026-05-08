<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Product;

class AttributeList
{
    protected array $attributes = [];

    public function __construct(
        protected \MageSuite\GoogleStructuredData\Provider\Data\Product\CompositeAttribute $compositeAttributeProvider,
        protected array $attributeList = []
    ) {}

    public function getList(?int $storeId = null): array
    {
        $cacheKey = $storeId ?? 0;

        if (isset($this->attributes[$cacheKey])) {
            return $this->attributes[$cacheKey];
        }

        $hardcoded = array_values($this->attributeList);
        $configured = array_values($this->compositeAttributeProvider->getEavAttributeCodes($storeId));

        $this->attributes[$cacheKey] = array_values(array_unique(array_filter(
            array_merge($hardcoded, $configured)
        )));

        return $this->attributes[$cacheKey];
    }
}
