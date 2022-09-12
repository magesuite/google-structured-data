<?php

namespace MageSuite\GoogleStructuredData\Provider\Data\Product;

interface TypeResolverInterface
{
    public function isApplicable(string $productTypeId): bool;

    public function execute(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store, bool $withReviews = true): array;
}
