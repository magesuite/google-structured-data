<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Eav;

class GetAttributeValue
{
    protected array $attributeTextTypes = ['select', 'multiselect'];
    protected array $attributesCache = [];

    public function __construct(
        protected \Magento\Eav\Model\Entity\Attribute $attribute
    ) {}

    public function execute(\Magento\Catalog\Api\Data\ProductInterface $product, string $attributeCode) // phpcs:ignore
    {
        $attribute = $this->getAttribute($attributeCode);

        if (in_array($attribute->getFrontendInput(), $this->attributeTextTypes)) {
            return $product->getAttributeText($attributeCode);
        }

        return $product->getData($attributeCode);
    }

    protected function getAttribute(string $attributeCode): \Magento\Eav\Model\Entity\Attribute
    {
        if (!isset($this->attributesCache[$attributeCode])) {
            $attribute = $this->attribute->loadByCode(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
            $this->attributesCache[$attributeCode] = clone $attribute;
        }

        return $this->attributesCache[$attributeCode];
    }
}
