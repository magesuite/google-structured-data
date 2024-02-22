<?php

namespace MageSuite\GoogleStructuredData\Plugin\GroupedProduct\Model\Product\Type\Grouped;

class AddAttributesToAssociatedProducts
{
    protected \MageSuite\GoogleStructuredData\Provider\Data\Product\CompositeAttribute $compositeAttributeDataProvider;

    protected array $attributesToSelect;

    public function __construct(
        \MageSuite\GoogleStructuredData\Provider\Data\Product\CompositeAttribute $compositeAttributeDataProvider,
        array $attributesToSelect = []
    ) {
        $this->compositeAttributeDataProvider = $compositeAttributeDataProvider;
        $this->attributesToSelect = $attributesToSelect;
    }

    public function afterGetAssociatedProductCollection(\Magento\GroupedProduct\Model\Product\Type\Grouped $subject, $result)
    {
        $attributesToSelect = array_filter(array_values($this->compositeAttributeDataProvider->getEavAttributeCodes()));
        $attributesToSelect = array_merge(array_values($this->attributesToSelect), $attributesToSelect);

        $result->addAttributeToSelect($attributesToSelect);

        return $result;
    }
}
