<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data\Product;

class AdditionalPropertyPool
{
    public function __construct(
        protected \Magento\Framework\Escaper $escaper,
        protected array $providers = []
    ) {}

    public function getAttributeCodes(): array
    {
        return array_values(array_unique(array_merge([], ...array_map(static fn ($provider): array => $provider->getAttributeCodes(), array_values($this->providers)))));
    }

    public function getAdditionalProperties(\Magento\Catalog\Api\Data\ProductInterface $product, int $storeId): array
    {
        $additionalProperties = array_map(static fn ($provider): array => $provider->getAdditionalProperties($product, $storeId), array_values($this->providers));

        return array_map(fn (array $additionalProperty): array => $this->escapeAdditionalProperty($additionalProperty), array_merge([], ...$additionalProperties));
    }

    protected function escapeAdditionalProperty(array $additionalProperty): array
    {
        return array_map(fn (mixed $value): mixed => is_string($value) ? $this->escaper->escapeHtml($value) : $value, $additionalProperty);
    }
}
