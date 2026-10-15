<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Product;

class GalleryImageUrls
{
    protected array $imageParams = [];

    public function __construct(
        protected \Magento\Framework\View\ConfigInterface $presentationConfig,
        protected \Magento\Catalog\Model\Product\Image\ParamsBuilder $imageParamsBuilder,
        protected \Magento\Catalog\Model\View\Asset\ImageFactory $viewAssetImageFactory,
        protected string $imageId = 'product_page_image_large'
    ) {}

    public function getUrl(string $file, int $storeId): string
    {
        return $this->viewAssetImageFactory->create(['miscParams' => $this->getImageParams($storeId), 'filePath' => $file])->getUrl();
    }

    protected function getImageParams(int $storeId): array
    {
        if (isset($this->imageParams[$storeId])) {
            return $this->imageParams[$storeId];
        }

        $imageArguments = $this->presentationConfig->getViewConfig()->getMediaAttributes('Magento_Catalog', \Magento\Catalog\Helper\Image::MEDIA_TYPE_CONFIG_NODE, $this->imageId);
        $this->imageParams[$storeId] = $this->imageParamsBuilder->build($imageArguments, $storeId);

        return $this->imageParams[$storeId];
    }
}
