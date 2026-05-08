<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data\Product\Attribute;

class Description implements \MageSuite\GoogleStructuredData\Provider\Data\Product\AttributeInterface
{
    public function __construct(
        protected \Magento\Framework\Filter\StripTags $stripTags
    ) {}

    public function getAttributeData(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        ?string $attributeCode
    ): string|array|null
    {
        $description = $product->getData($attributeCode) ?: $product->getData('description');

        if (empty($description)) {
            return null;
        }

        return $this->stripTags->filter($description);
    }
}
