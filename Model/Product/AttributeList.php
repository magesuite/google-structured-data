<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Product;

class AttributeList
{
    protected ?array $attributes = null;

    public function __construct(
        protected \MageSuite\GoogleStructuredData\Provider\Data\Product\CompositeAttribute $compositeAttributeProvider,
        protected array $attributeList = []
    ) {}

    public function getList(): array
    {
        if ($this->attributes !== null) {
            return $this->attributes;
        }

        $this->attributes = array_filter(array_values(array_merge(
            $this->compositeAttributeProvider->getEavAttributeCodes(),
            $this->attributeList
        )));

        return $this->attributes;
    }
}
