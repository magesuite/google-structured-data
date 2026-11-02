<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Product;

class GalleryImageUrls
{
    public const IMAGE_URL_BEFORE_EVENT = 'google_structured_data_product_image_url_before';

    protected array $imageParams = [];

    public function __construct(
        protected \Magento\Framework\View\ConfigInterface $presentationConfig,
        protected \Magento\Catalog\Model\Product\Image\ParamsBuilder $imageParamsBuilder,
        protected \Magento\Catalog\Model\View\Asset\ImageFactory $viewAssetImageFactory,
        protected \Magento\Framework\Event\ManagerInterface $eventManager,
        protected string $imageId = 'product_page_image_large'
    ) {}

    public function getUrl(string $file, int $fileSize, int $storeId): string
    {
        $this->eventManager->dispatch(self::IMAGE_URL_BEFORE_EVENT, [
            \MageSuite\GoogleStructuredData\Model\Product\GalleryImageFiles::FILE => $file,
            \MageSuite\GoogleStructuredData\Model\Product\GalleryImageFiles::FILE_SIZE => $fileSize,
        ]);

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
