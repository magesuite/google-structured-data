<?php

namespace MageSuite\GoogleStructuredData\Provider\Data\Product;

interface TypeResolverInterface
{
    /**
     * @param string $productTypeId
     * @return bool
     */
    public function isApplicable($productTypeId);

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @param bool $withReviews
     * @return array
     */
    public function execute(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store, bool $withReviews = true);
}
