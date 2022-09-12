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
        $this->productTypeResolvers = $productTypeResolvers;
        $this->defaultResolver = $defaultResolver;
    }

    public function getProductTypeResolver($productTypeId)
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
