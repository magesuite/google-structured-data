<?php

namespace MageSuite\GoogleStructuredData\Provider\Data\Product;

interface AttributeInterface
{
    public function getAttributeData(\Magento\Catalog\Api\Data\ProductInterface $product, string $attributeCode);
}
