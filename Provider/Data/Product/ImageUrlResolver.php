<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data\Product;

class ImageUrlResolver
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Model\Product\GalleryImageUrls $galleryImageUrls
    ) {}

    public function resolve(array $productData, int $storeId): array
    {
        if ($this->hasImageFiles($productData)) {
            $productData['image'] = $this->resolveImages($productData['image'], $storeId);
        }

        foreach ($productData as $key => $value) {
            if ($key === 'image' || !is_array($value)) {
                continue;
            }

            $productData[$key] = $this->resolve($value, $storeId);
        }

        return $productData;
    }

    protected function hasImageFiles(array $node): bool
    {
        return ($node['@type'] ?? null) === 'Product' && isset($node['image']) && is_array($node['image']);
    }

    protected function resolveImages(array $images, int $storeId): array
    {
        return array_map(fn(string $image): string => $this->isUrl($image) ? $image : $this->galleryImageUrls->getUrl($image, $storeId), $images);
    }

    protected function isUrl(string $image): bool
    {
        return preg_match('#^https?://#i', $image) === 1;
    }
}
