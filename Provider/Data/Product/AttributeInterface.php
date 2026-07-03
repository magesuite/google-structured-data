<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data\Product;

interface AttributeInterface
{
    public function getAttributeData(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        ?string $attributeCode
    ): string|array|null;
}
