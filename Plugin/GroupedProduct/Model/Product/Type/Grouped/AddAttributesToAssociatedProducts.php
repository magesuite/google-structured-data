<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Plugin\GroupedProduct\Model\Product\Type\Grouped;

class AddAttributesToAssociatedProducts
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Provider\Data\Product\CompositeAttribute $compositeAttributeDataProvider,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
        protected array $attributesToSelect = []
    ) {}

    public function afterGetAssociatedProductCollection(
        \Magento\GroupedProduct\Model\Product\Type\Grouped $subject,
        \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection $result
    ): \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection {
        $storeId = (int)($result->getStoreId() ?: $this->storeManager->getStore()->getId());
        $attributesToSelect = array_merge(
            $this->attributesToSelect,
            array_filter($this->compositeAttributeDataProvider->getEavAttributeCodes($storeId))
        );

        $result->addAttributeToSelect($attributesToSelect);

        return $result;
    }
}
