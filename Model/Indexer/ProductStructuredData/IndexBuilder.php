<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Indexer\ProductStructuredData;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexBuilder
{
    public const DEFAULT_BUNCH_SIZE = 500;

    public function __construct(
        protected \Magento\Framework\Indexer\CacheContext $cacheContext,
        protected \Magento\Framework\Serialize\SerializerInterface $serializer,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
        protected \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        protected \Magento\Catalog\Model\Config $catalogConfig,
        protected \MageSuite\GoogleStructuredData\Provider\Data\Product\CompositeAttribute $compositeAttributeProvider,
        protected \MageSuite\GoogleStructuredData\Model\ResourceModel\Index $indexResourceModel,
        protected \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider,
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $configuration,
        protected \Psr\Log\LoggerInterface $logger,
        protected int $bunchSize = self::DEFAULT_BUNCH_SIZE
    ) {}

    public function reindexList(array $productIds): void
    {
        foreach ($this->storeManager->getStores(false) as $store) {
            if (!$store->isActive()) {
                continue;
            }

            foreach ($this->getProducts($productIds, $store) as $products) {
                $this->buildIndex($products, $store);
            }
        }
    }

    /**
     * @return \Magento\Catalog\Model\Product[]
     */
    public function getProducts(array $ids, \Magento\Store\Api\Data\StoreInterface $store): \Generator
    {
        foreach (array_chunk($ids, $this->bunchSize) as $idsChunk) {
            $collection = $this->productCollectionFactory->create();
            $collection->addStoreFilter($store);
            $collection->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
            $collection->addAttributeToSelect($this->getAttributeList($store));
            $collection->addIdFilter($idsChunk);
            $collection->addUrlRewrite();
            $collection->addMediaGalleryData();

            yield $collection->getItems();
        }
    }

    public function getAttributeList(\Magento\Store\Api\Data\StoreInterface $store): array
    {
        $attributeList = $this->configuration->getAttributeList((int) $store->getId());
        if ($attributeList) {
            return $attributeList;
        }

        return array_unique(array_merge(
            $this->compositeAttributeProvider->getEavAttributeCodes(),
            $this->catalogConfig->getProductAttributes()
        ));
    }

    protected function buildIndex(array $products, \Magento\Store\Api\Data\StoreInterface $store): void
    {
        $generatedData = [];

        foreach ($products as $product) {
            $productData = $this->productDataProvider->generateProductData($product, $store);
            $generatedData[] = [
                'product_id' => (int)$product->getId(),
                'store_id' => (int)$store->getId(),
                'data' => $this->serializer->serialize($productData)
            ];
        }

        if (empty($generatedData)) {
            return;
        }

        $productIds = array_column($generatedData, 'product_id');

        try {
            $this->indexResourceModel->startTransaction();
            $this->indexResourceModel->deleteByProductId($productIds, (int)$store->getId());
            $this->indexResourceModel->insert($generatedData);
            $this->cacheContext->registerEntities(\Magento\Catalog\Model\Product::CACHE_TAG, $productIds);
            $this->indexResourceModel->commit();
        } catch (\Throwable $e) {
            $this->logger->error('There has been an error when reindexing structured data: ' . $e->getMessage());
            $this->indexResourceModel->rollBack();
        }
    }
}
