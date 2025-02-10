<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data\Product;

interface ModifierInterface
{
    public function isEnabled(): bool;

    public function execute(array $productData, \Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store): array;
}
