<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data;

class Product
{
    public const CACHE_KEY = 'google_structured_data_product_%s_%s_%s';
    public const CACHE_GROUP = 'google_structured_data_product';

    public function __construct(
        protected \Magento\Framework\Serialize\SerializerInterface $serializer,
        protected \Magento\Framework\App\CacheInterface $cache,
        protected \MageSuite\GoogleStructuredData\Model\ProductStructuredDataIndexRepository $productStructuredDataIndexRepository,
        protected \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverPool $productTypeResolverPool,
        protected \MageSuite\GoogleStructuredData\Provider\Data\Product\ModifiersPool $modifiersPool,
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration,
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Category $categoryConfiguration
    ) {}

    public function getProductData(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store): array
    {
        $cacheKey = $this->getCacheKey($product, $store);
        $cacheLifetime = $this->productConfiguration->getCacheLifetime();

        if ($cacheLifetime > 0) {
            $cachedData = $this->cache->load($cacheKey);

            if ($cachedData) {
                return $this->serializer->unserialize($cachedData);
            }
        }

        $productData = $this->getProductsData($product, $store);

        if (empty($productData)) {
            return $productData;
        }

        foreach ($this->modifiersPool->getModifiers() as $modifier) {
            /** @var \MageSuite\GoogleStructuredData\Provider\Data\Product\ModifierInterface $modifier */
            $modifier = $modifier['modifier'];
            $productData = $modifier->execute($productData, $product, $store);
        }

        if (!$cacheLifetime) {
            return $productData;
        }

        $identities = $this->getIdentities($product);

        $this->cache->save(
            $this->serializer->serialize($productData),
            $cacheKey,
            $identities,
            $cacheLifetime
        );

        return $productData;
    }

    public function generateProductData(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store): array
    {
        $productTypeResolver = $this->productTypeResolverPool->getProductTypeResolver($product->getTypeId());

        return $productTypeResolver->execute($product, $store);
    }

    public function getCacheKey(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store): string
    {
        return sprintf(
            self::CACHE_KEY,
            $product->getId(),
            $store->getId(),
            $store->getCurrentCurrencyCode()
        );
    }

    public function getIdentities(\Magento\Catalog\Api\Data\ProductInterface $product): array
    {
        $identities = $product->getIdentities();
        $identities[] = self::CACHE_GROUP;
        $key = array_search(\Magento\Catalog\Model\Product::CACHE_TAG, $identities);

        if (!$key) {
            return $identities;
        }

        unset($identities[$key]);

        return $identities;
    }

    public function getListItemData(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store, int $position): array
    {
        if (!$this->categoryConfiguration->shouldEmbedFullProductData()) {
            return $this->getUrlListItemData($product, $position);
        }

        $productData = $this->getProductData($product, $store);
        $shouldShowRating = $this->categoryConfiguration->shouldShowRating();
        $removeRating = function (&$item) use ($shouldShowRating): void {
            if (!$shouldShowRating) {
                unset($item['review'], $item['aggregateRating']);
            }
        };

        if (isset($productData['@type']) && $productData['@type'] === 'Product') {
            $removeRating($productData);

            return [
                "@type" => "ListItem",
                "position" => $position,
                "item" => [$productData],
            ];
        }

        $productData = array_filter($productData, function ($item): bool {
            return isset($item['@type']) && $item['@type'] === 'Product';
        });
        array_walk($productData, $removeRating);

        return [
            "@type" => "ListItem",
            "position" => $position,
            "item" => $productData
        ];
    }

    protected function getUrlListItemData(\Magento\Catalog\Api\Data\ProductInterface $product, int $position): array
    {
        return [
            "@type" => "ListItem",
            "position" => $position,
            "name" => $product->getName(),
            "url" => $product->getProductUrl()
        ];
    }

    public function getProductsData(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store): array
    {
        $productData = [];

        if ($this->productConfiguration->isIndexingEnabled()) {
            $productData = $this->productStructuredDataIndexRepository->getDataFromIndex(
                (int)$product->getId(),
                (int)$store->getId()
            );
        }

        return empty($productData) ? $this->generateProductData($product, $store) : $productData;
    }
}
