<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Product;

class GalleryImageFiles
{
    public function getFiles(\Magento\Catalog\Api\Data\ProductInterface $product): array
    {
        $mediaGallery = $product->getMediaGallery('images');

        if (!is_array($mediaGallery) || empty($mediaGallery)) {
            return [];
        }

        $files = [];

        foreach ($mediaGallery as $image) {
            $file = $image['file'] ?? '';

            if (!empty($image['disabled']) || $file === '') {
                continue;
            }

            $files[] = $file;
        }

        return $files;
    }
}
