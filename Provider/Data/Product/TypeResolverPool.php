<?php

namespace MageSuite\GoogleStructuredData\Provider\Data\Product;

class TypeResolverPool
{
    protected \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolver\DefaultResolver $defaultResolver;

    protected array $productTypeResolvers;

    public function __construct(
        \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolver\DefaultResolver $defaultResolver,
        array $productTypeResolvers
    ) {
        $this->defaultResolver = $defaultResolver;
        $this->productTypeResolvers = $productTypeResolvers;
    }

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
