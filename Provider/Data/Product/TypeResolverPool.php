<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data\Product;

class TypeResolverPool
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolver\DefaultResolver $defaultResolver,
        protected array $productTypeResolvers
    ) {}

    public function getProductTypeResolver(string $productTypeId): \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverInterface
    {
        foreach ($this->productTypeResolvers as $productTypeResolver) {
            if (!$productTypeResolver->isApplicable($productTypeId)) {
                continue;
            }

            return $productTypeResolver;
        }

        return $this->defaultResolver;
    }
}
