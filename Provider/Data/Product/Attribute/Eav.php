<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data\Product\Attribute;

class Eav implements \MageSuite\GoogleStructuredData\Provider\Data\Product\AttributeInterface
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Model\Eav\GetAttributeValue $getAttributeValue
    ) {}

    public function getAttributeData(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        ?string $attributeCode
    ): string|array|null
    {
        if (!$attributeCode) {
            return null;
        }

        return $this->getAttributeValue->execute($product, $attributeCode);
    }
}
