<?php

namespace MageSuite\GoogleStructuredData\Provider\Data\Product\Attribute;

class GenericConfigured implements \MageSuite\GoogleStructuredData\Provider\Data\Product\AttributeInterface
{
    /**
     * @var \MageSuite\GoogleStructuredData\Helper\Configuration\Product
     */
    protected $productConfiguration;

    /**
     * @var \MageSuite\GoogleStructuredData\Provider\Data\Product\Attribute\Generic $genericAttributeProvider
     */
    protected $genericAttributeProvider;

    public function __construct(
        \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration,
        \MageSuite\GoogleStructuredData\Provider\Data\Product\Attribute\Generic $genericAttributeProvider
    ) {
        $this->productConfiguration = $productConfiguration;
        $this->genericAttributeProvider = $genericAttributeProvider;
    }

    public function getAttributeData(\Magento\Catalog\Api\Data\ProductInterface $product, string $attributeCode)
    {
        $attributeCode = $this->productConfiguration->getConfiguredAttribute($attributeCode);
        if (!$attributeCode) {
            return null;
        }

        return $this->genericAttributeProvider->getAttributeData($product, $attributeCode);
    }
}
