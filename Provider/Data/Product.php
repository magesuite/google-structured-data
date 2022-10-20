<?php

namespace MageSuite\GoogleStructuredData\Provider\Data;

class Product
{
    const CACHE_KEY = 'google_structured_data_product_%s_%s_%s';
    const CACHE_GROUP = 'google_structured_data_product';

    protected \Magento\Framework\Serialize\SerializerInterface $serializer;

    protected \Magento\Framework\App\CacheInterface $cache;

    protected \Magento\Store\Model\StoreManagerInterface $storeManager;

    protected \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverPool $productTypeResolverPool;

    public function __construct(
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverPool $productTypeResolverPool
    ) {
        $this->serializer = $serializer;
        $this->cache = $cache;
        $this->storeManager = $storeManager;
        $this->productTypeResolverPool = $productTypeResolverPool;
    }

    public function execute(\Magento\Catalog\Api\Data\ProductInterface $product, bool $withReviews = true): array
    {
        $store = $this->storeManager->getStore();
        $cacheKey = $this->getCacheKey($product, $store, $withReviews);

        if (($cachedData = $this->cache->load($cacheKey))) {
            return $this->serializer->unserialize($cachedData);
        }

        $productTypeResolver = $this->productTypeResolverPool->getProductTypeResolver($product->getTypeId());
        $productData = $productTypeResolver->execute($product, $store, $withReviews);

        $identities = $this->getIdentities($product);

        $this->cache->save(
            $this->serializer->serialize($productData),
            $cacheKey,
            $identities
        );

        return $productData;
    }

    public function getCacheKey(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store, bool $withReviews = true)
    {
        return sprintf(
            self::CACHE_KEY,
            $product->getId(),
            $store->getId(),
            $withReviews
        );
    }

    public function getIdentities(\Magento\Catalog\Api\Data\ProductInterface $product)
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
}
