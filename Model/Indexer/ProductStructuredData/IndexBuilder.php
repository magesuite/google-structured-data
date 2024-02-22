<?php

namespace MageSuite\GoogleStructuredData\Model\Indexer\ProductStructuredData;

class IndexBuilder
{
    public const DEFAULT_BUNCH_SIZE = 100;

    protected int $bunchSize;

    protected \Magento\Framework\Indexer\CacheContext $cacheContext;
    protected \Magento\Framework\Serialize\SerializerInterface $serializer;
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;
    protected \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory;
    protected \MageSuite\GoogleStructuredData\Model\ResourceModel\Index $indexResourceModel;
    protected \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider;
    protected \Psr\Log\LoggerInterface $logger;

    public function __construct(
        \Magento\Framework\Indexer\CacheContext $cacheContext,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \MageSuite\GoogleStructuredData\Model\ResourceModel\Index $indexResourceModel,
        \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider,
        \Psr\Log\LoggerInterface $logger,
        $bunchSize = self::DEFAULT_BUNCH_SIZE
    ) {
        $this->cacheContext = $cacheContext;
        $this->storeManager = $storeManager;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->indexResourceModel = $indexResourceModel;
        $this->productDataProvider = $productDataProvider;
        $this->logger = $logger;
        $this->bunchSize = $bunchSize;
        $this->serializer = $serializer;
    }

    public function reindexList(array $productIds): void
    {
        $stores = $this->storeManager->getStores();

        foreach ($this->getProducts($productIds) as $products) {
            $this->buildIndex($products, $stores);
        }
    }

    /**
     * @return \Magento\Catalog\Model\Product[]
     */
    public function getProducts(array $ids)
    {
        foreach (array_chunk($ids, $this->bunchSize) as $idsChunk) {
            $collection = $this->productCollectionFactory->create();
            $collection->addAttributeToSelect('*');
            $collection->addIdFilter($idsChunk);

            yield $collection->getItems();
        }
    }

    protected function buildIndex(array $products, array $stores): void
    {
        $generatedData = [];

        foreach ($stores as $store) {
            foreach ($products as $product) {
                $productData = $this->productDataProvider->generateProductData($product, $store);

                $generatedData[] = [
                    'product_id' => $product->getId(),
                    'store_id' => $store->getId(),
                    'data' => $this->serializer->serialize($productData)
                ];
            }
        }

        $productIds = array_column($generatedData, 'product_id');

        try {
            $this->indexResourceModel->startTransaction();

            $this->indexResourceModel->deleteByProductId($productIds);
            $this->indexResourceModel->insert($generatedData);

            $this->cacheContext->registerEntities(\Magento\Catalog\Model\Product::CACHE_TAG, $productIds);

            $this->indexResourceModel->commit();
        } catch (\Throwable $e) {
            $this->logger->error('There has been an error when reindexing structured data: ' . $e->getMessage());
            $this->indexResourceModel->rollBack();
        }
    }
}
