<?php

namespace MageSuite\GoogleStructuredData\Provider\Data\Product\Attribute;

class Eav implements \MageSuite\GoogleStructuredData\Provider\Data\Product\AttributeInterface
{
    /**
     * @var \MageSuite\GoogleStructuredData\Model\Eav\GetAttributeValue
     */
    protected $getAttributeValue;

    public function __construct(\MageSuite\GoogleStructuredData\Model\Eav\GetAttributeValue $getAttributeValue)
    {
        $this->getAttributeValue = $getAttributeValue;
    }

    public function getAttributeData(\Magento\Catalog\Api\Data\ProductInterface $product, ?string $attributeCode)
    {
        if (!$attributeCode) {
            return null;
        }

        return $this->getAttributeValue->execute($product, $attributeCode);
    }
}
