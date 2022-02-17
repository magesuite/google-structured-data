<?php

namespace MageSuite\GoogleStructuredData\Model\Product\Grouped;

class GetAssociatedProducts
{
    /**
     * @var array
     */
    protected $defaultAttributesToSelect = ['name', 'price', 'special_price', 'special_from_date', 'special_to_date', 'tax_class_id', 'image', 'url_key'];

    public function execute(\Magento\Catalog\Api\Data\ProductInterface $product, $attributesToSelect = [])
    {
        $attributesToSelect = array_filter(array_values($attributesToSelect));
        $attributesToSelect = array_merge($this->defaultAttributesToSelect, $attributesToSelect);
        $productType = $product->getTypeInstance();
        $productType->setSaleableStatus($product);

        return $productType->getAssociatedProductCollection($product)
            ->addAttributeToSelect($attributesToSelect)
            ->addFilterByRequiredOptions()
            ->setPositionOrder()
            ->addStoreFilter($productType->getStoreFilter($product))
            ->addAttributeToFilter('status', ['in' => $productType->getStatusFilters($product)]);
    }
}
