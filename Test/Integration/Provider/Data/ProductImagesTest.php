<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Test\Integration\Provider\Data;

#[\Magento\TestFramework\Fixture\AppArea('frontend')]
#[\Magento\TestFramework\Fixture\DbIsolation(true)]
#[\Magento\TestFramework\Fixture\AppIsolation(true)]
class ProductImagesTest extends \PHPUnit\Framework\TestCase
{
    protected const IMAGE_FILE = '/m/a/magento_image.jpg';

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
    public function testGeneratedDataKeepsImageFilesSoUrlsAreNotPersisted(): void
    {
        $product = $this->productRepository->get('simple');

        $productData = $this->productDataProvider->generateProductData($product, $this->storeManager->getStore());

        $this->assertSame([self::IMAGE_FILE], $productData['image']);
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
}
