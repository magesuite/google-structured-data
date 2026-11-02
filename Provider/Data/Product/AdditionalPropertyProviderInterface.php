<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data\Product;

interface AdditionalPropertyProviderInterface
{
    public function getAttributeCodes(): array;

    public function getAdditionalProperties(\Magento\Catalog\Api\Data\ProductInterface $product, int $storeId): array;
}
