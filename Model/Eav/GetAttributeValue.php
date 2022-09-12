<?php

namespace MageSuite\GoogleStructuredData\Model\Eav;

class GetAttributeValue
{
    protected \Magento\Eav\Model\Entity\Attribute $attribute;

    protected array $attributeTextTypes = ['select', 'multiselect'];

    protected array $attributesCache = [];

    public function __construct(\Magento\Eav\Model\Entity\Attribute $attribute)
    {
        $this->attribute = $attribute;
    }

    public function execute(\Magento\Catalog\Api\Data\ProductInterface $product, $attributeCode)
    {
        $attribute = $this->getAttribute($attributeCode);

        if (in_array($attribute->getFrontendInput(), $this->attributeTextTypes)) {
            return $product->getAttributeText($attributeCode);
        }

        return $product->getData($attributeCode);
    }

    protected function getAttribute($attributeCode)
    {
        if (!isset($this->attributesCache[$attributeCode])) {
            $attribute = $this->attribute->loadByCode(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
            $this->attributesCache[$attributeCode] = clone $attribute;
        }

        return $this->attributesCache[$attributeCode];
    }
}
