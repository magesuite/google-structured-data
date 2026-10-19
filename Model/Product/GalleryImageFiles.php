<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Product;

class GalleryImageFiles
{
    public const FILE = 'file';
    public const FILE_SIZE = 'file_size';

    public function getImages(\Magento\Catalog\Api\Data\ProductInterface $product): array
    {
        $mediaGallery = $product->getMediaGallery('images');

        if (!is_array($mediaGallery) || empty($mediaGallery)) {
            return [];
        }

        $images = [];

        foreach ($mediaGallery as $image) {
            $file = $image[self::FILE] ?? '';

            if (!empty($image['disabled']) || $file === '') {
                continue;
            }

            $images[] = [
                self::FILE => $file,
                self::FILE_SIZE => (int)($image[self::FILE_SIZE] ?? 0),
            ];
        }

        return $images;
    }
}
