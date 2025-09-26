<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Plugin\GroupedProduct\Model\Product\Type\Grouped;

class AddAttributesToAssociatedProducts
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Provider\Data\Product\CompositeAttribute $compositeAttributeDataProvider,
        protected array $attributesToSelect = []
    ) {}

    public function afterGetAssociatedProductCollection(
        \Magento\GroupedProduct\Model\Product\Type\Grouped $subject,
        \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection $result
    ): \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection {
        $attributesToSelect = array_filter(array_values($this->compositeAttributeDataProvider->getEavAttributeCodes()));
        $attributesToSelect = array_merge(array_values($this->attributesToSelect), $attributesToSelect);

        $result->addAttributeToSelect($attributesToSelect);

        return $result;
    }
}
