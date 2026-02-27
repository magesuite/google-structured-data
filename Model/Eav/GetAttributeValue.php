<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Eav;

class GetAttributeValue
{
    protected array $attributeTextTypes = ['select', 'multiselect'];

    public function __construct(
        protected \Magento\Eav\Model\Config $eavConfig
    ) {}

    public function execute(\Magento\Catalog\Api\Data\ProductInterface $product, string $attributeCode) // phpcs:ignore
    {
        $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);

        if (in_array($attribute->getFrontendInput(), $this->attributeTextTypes)) {
            return $product->getAttributeText($attributeCode);
        }

        return $product->getData($attributeCode);
    }
}
