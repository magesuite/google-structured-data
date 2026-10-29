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
        return array_map(fn(string|array $image): string => is_array($image) ? $this->getImageUrl($image, $storeId) : $image, $images);
    }

    protected function getImageUrl(array $image, int $storeId): string
    {
        $file = $image[\MageSuite\GoogleStructuredData\Model\Product\GalleryImageFiles::FILE];
        $fileSize = (int)$image[\MageSuite\GoogleStructuredData\Model\Product\GalleryImageFiles::FILE_SIZE];

        return $this->galleryImageUrls->getUrl($file, $fileSize, $storeId);
    }
}
