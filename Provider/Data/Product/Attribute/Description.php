<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data\Product\Attribute;

class Description implements \MageSuite\GoogleStructuredData\Provider\Data\Product\AttributeInterface
{
    public function __construct(
        protected \Magento\Framework\Filter\StripTags $stripTags
    ) {}

    public function getAttributeData(\Magento\Catalog\Api\Data\ProductInterface $product, ?string $attributeCode) // phpcs:ignore
    {
        $description = $product->getDescription();

        if (empty($description)) {
            return null;
        }

        return $this->stripTags->filter($description);
    }
}
