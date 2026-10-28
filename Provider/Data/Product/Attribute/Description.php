<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data\Product\Attribute;

class Description implements \MageSuite\GoogleStructuredData\Provider\Data\Product\AttributeInterface
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Model\Product\DescriptionFilter $descriptionFilter
    ) {}

    public function getAttributeData(\Magento\Catalog\Api\Data\ProductInterface $product, ?string $attributeCode): string|array|null
    {
        $description = $product->getData($attributeCode) ?: $product->getData('description');

        if (empty($description)) {
            return null;
        }

        return $this->descriptionFilter->filter((string)$description, (int)$product->getStoreId());
    }
}
