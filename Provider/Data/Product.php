<?php

namespace MageSuite\GoogleStructuredData\Provider\Data;

class Product
{
    const CACHE_KEY = 'google_structured_data_product_%s_%s';
    const CACHE_GROUP = 'google_structured_data_product';

    protected \Magento\Framework\Serialize\SerializerInterface $serializer;
    protected \Magento\Framework\App\CacheInterface $cache;
    protected \MageSuite\GoogleStructuredData\Model\ProductStructuredDataIndexRepository $productStructuredDataIndexRepository;
    protected \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverPool $productTypeResolverPool;
    protected \MageSuite\GoogleStructuredData\Provider\Data\Product\ModifiersPool $modifiersPool;
    protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration;

    public function __construct(
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\App\CacheInterface $cache,
        \MageSuite\GoogleStructuredData\Model\ProductStructuredDataIndexRepository $productStructuredDataIndexRepository,
        \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverPool $productTypeResolverPool,
        \MageSuite\GoogleStructuredData\Provider\Data\Product\ModifiersPool $modifiersPool,
        \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration
    ) {
        $this->serializer = $serializer;
        $this->cache = $cache;
        $this->productStructuredDataIndexRepository = $productStructuredDataIndexRepository;
        $this->productTypeResolverPool = $productTypeResolverPool;
        $this->modifiersPool = $modifiersPool;
        $this->productConfiguration = $productConfiguration;
    }

    public function getProductData(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store): array
    {
        $cacheKey = $this->getCacheKey($product, $store);

        if (($cachedData = $this->cache->load($cacheKey))) {
            return $this->serializer->unserialize($cachedData);
        }

        if ($this->productConfiguration->isIndexingEnabled()) {
            $productData = $this->productStructuredDataIndexRepository->getDataFromIndex($product->getId(), $store->getId());
        } else {
            $productData = $this->generateProductData($product, $store);
        }

        foreach ($this->modifiersPool->getModifiers() as $modifier) {
            /** @var \MageSuite\GoogleStructuredData\Provider\Data\Product\ModifierInterface $modifier */
            $modifier = $modifier['modifier'];
            $productData = $modifier->execute($productData, $product, $store);
        }

        $identities = $this->getIdentities($product);

        $this->cache->save(
            $this->serializer->serialize($productData),
            $cacheKey,
            $identities
        );

        return $productData;
    }

    public function generateProductData(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store): array
    {
        $productTypeResolver = $this->productTypeResolverPool->getProductTypeResolver($product->getTypeId());

        return $productTypeResolver->execute($product, $store);
    }

    public function getCacheKey(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store)
    {
        return sprintf(
            self::CACHE_KEY,
            $product->getId(),
            $store->getId()
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
