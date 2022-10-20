<?php

namespace MageSuite\GoogleStructuredData\Provider\Data\Product\Attribute;

class Brand extends \MageSuite\GoogleStructuredData\Provider\Data\Product\Attribute\Eav
{
    public function getAttributeData(\Magento\Catalog\Api\Data\ProductInterface $product, ?string $attributeCode)
    {
        $value = parent::getAttributeData($product, $attributeCode);

        if (!$value) {
            return $value;
        }

        return ['@type' => 'Brand', 'name' => $value];
    }
}
