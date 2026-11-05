<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Test\Integration\Provider\Data;

#[\Magento\TestFramework\Fixture\AppArea('frontend')]
#[\Magento\TestFramework\Fixture\DbIsolation(true)]
#[\Magento\TestFramework\Fixture\AppIsolation(true)]
class ProductImagesTest extends \PHPUnit\Framework\TestCase
{
    protected const IMAGE_FILE = '/m/a/magento_image.jpg';
    protected const IMAGE_FILE_SIZE = 4321;

    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\Magento\Framework\App\CacheInterface $cache;
    protected ?\Magento\Store\Model\StoreManagerInterface $storeManager;
    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository;
    protected ?\MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->cache = $this->objectManager->get(\Magento\Framework\App\CacheInterface::class);
        $this->storeManager = $this->objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->productDataProvider = $this->objectManager->get(\MageSuite\GoogleStructuredData\Provider\Data\Product::class);
    }

    protected function tearDown(): void
    {
        $this->cache->clean([\MageSuite\GoogleStructuredData\Provider\Data\Product::CACHE_GROUP]);
    }

    #[\Magento\TestFramework\Fixture\DataFixture('Magento/Catalog/_files/product_with_image.php')]
    public function testGeneratedDataKeepsImageFilesWithSizesSoUrlsAreNotPersisted(): void
    {
        $product = $this->productRepository->get('simple', false, null, true);
        $product->setData('media_gallery', ['images' => [
            ['file' => self::IMAGE_FILE, 'file_size' => self::IMAGE_FILE_SIZE],
            ['file' => '/d/i/disabled_image.jpg', 'file_size' => self::IMAGE_FILE_SIZE, 'disabled' => 1],
        ]]);

        $productData = $this->productDataProvider->generateProductData($product, $this->storeManager->getStore());

        $this->assertSame([['file' => self::IMAGE_FILE, 'file_size' => self::IMAGE_FILE_SIZE]], $productData['image']);
    }

    public function testIndexedImageUrlIsBuiltWithPersistedFileSize(): void
    {
        $galleryImageUrls = $this->createMock(\MageSuite\GoogleStructuredData\Model\Product\GalleryImageUrls::class);
        $galleryImageUrls->expects($this->once())->method('getUrl')->with('/n/o/not_in_gallery.jpg', self::IMAGE_FILE_SIZE, 1)->willReturn('http://localhost/image.jpg');
        $imageUrlResolver = $this->objectManager->create(\MageSuite\GoogleStructuredData\Provider\Data\Product\ImageUrlResolver::class, ['galleryImageUrls' => $galleryImageUrls]);

        $productData = $imageUrlResolver->resolve([['@type' => 'Product', 'image' => [['file' => '/n/o/not_in_gallery.jpg', 'file_size' => self::IMAGE_FILE_SIZE]]]], 1);

        $this->assertSame(['http://localhost/image.jpg'], $productData[0]['image']);
    }

    #[\Magento\TestFramework\Fixture\DataFixture('Magento/Catalog/_files/product_with_image.php')]
    public function testProductDataReturnsSameImageUrlsAsProductPageGallery(): void
    {
        $product = $this->productRepository->get('simple');
        $galleryUrl = $this->objectManager->get(\Magento\Catalog\Model\Product\Image\UrlBuilder::class)->getUrl(self::IMAGE_FILE, 'product_page_image_large');

        $productData = $this->productDataProvider->getProductData($product, $this->storeManager->getStore());

        $this->assertSame([$galleryUrl], $productData['image']);
    }

    public function testImageUrlsFromPreviousIndexFormatAreKeptUntilReindex(): void
    {
        $imageUrl = 'http://localhost/media/catalog/product/m/a/magento_image.jpg';
        $imageUrlResolver = $this->objectManager->get(\MageSuite\GoogleStructuredData\Provider\Data\Product\ImageUrlResolver::class);

        $productData = $imageUrlResolver->resolve([['@type' => 'Product', 'image' => [$imageUrl]]], 1);

        $this->assertSame([$imageUrl], $productData[0]['image']);
    }

    public function testDispatchesImageFileWithSizeBeforeBuildingUrl(): void
    {
        $eventManager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $eventManager->expects($this->once())->method('dispatch')->with(\MageSuite\GoogleStructuredData\Model\Product\GalleryImageUrls::IMAGE_URL_BEFORE_EVENT, ['file' => self::IMAGE_FILE, 'file_size' => self::IMAGE_FILE_SIZE]);
        $galleryImageUrls = $this->objectManager->create(\MageSuite\GoogleStructuredData\Model\Product\GalleryImageUrls::class, ['eventManager' => $eventManager]);

        $galleryImageUrls->getUrl(self::IMAGE_FILE, self::IMAGE_FILE_SIZE, 1);
    }
}
