<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Indexer\Product\Action;

class Rows implements \Magento\Framework\Indexer\DimensionalIndexerInterface
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Model\Indexer\Product\DataProvider $dataProvider,
        protected \MageSuite\GoogleStructuredData\Model\Indexer\Product\TableMaintainer $tableMaintainer,
        protected \MageSuite\GoogleStructuredData\Model\ResourceModel\Indexer\Product\Action\Rows $rowsResourceModel,
        protected \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider,
        protected \Magento\Framework\Serialize\SerializerInterface $serializer,
        protected \Magento\Framework\App\ResourceConnection $resourceConnection,
        protected \Magento\Framework\Indexer\DimensionProviderInterface $dimensionProvider,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
        protected \MageSuite\GoogleStructuredData\Model\Review\BatchReviewData $batchReviewData
    ) {
    }

    public function execute(array $entityIds): void
    {
        if (!empty($entityIds)) {
            $entityIds = array_unique(array_merge($entityIds, $this->rowsResourceModel->getRelationsByChild($entityIds)));
        }

        foreach ($this->dimensionProvider->getIterator() as $dimension) {
            $this->executeByDimensions($dimension, new \ArrayIterator($entityIds));
        }
    }

    public function executeByDimensions(array $dimensions, \Traversable $entityIds): void
    {
        $entityIds = iterator_to_array($entityIds);

        foreach (array_chunk($entityIds, $this->dataProvider->getBatchSize()) as $entityIdsChunk) {
            $collection = $this->dataProvider->getProducts($dimensions, $entityIdsChunk);
            $this->prepareIndexTable($dimensions);
            $this->buildIndex($dimensions, $collection);
            $this->syncData($dimensions, $entityIdsChunk);
        }
    }

    protected function prepareIndexTable(array $dimensions): void
    {
        $this->tableMaintainer->createMainTmpTable($dimensions);
        $this->tableMaintainer->cleanTable(
            $this->tableMaintainer->getMainTmpTable($dimensions)
        );
    }

    protected function syncData(array $dimensions, array $entityIds): void
    {
        try {
            $this->resourceConnection->getConnection()->beginTransaction();

            $storeId = (int)$dimensions[\Magento\Store\Model\StoreDimensionProvider::DIMENSION_NAME]->getValue();
            $connection = $this->resourceConnection->getConnection();
            $connection->delete(
                $this->tableMaintainer->getMainTable(),
                [
                    'product_id IN (?)' => $entityIds,
                    'store_id = ?' => $storeId
                ]
            );
            $select = $connection->select()
                ->from(
                    $this->tableMaintainer->getMainTmpTable($dimensions),
                    ['*']
                );
            $connection->query(
                $connection->insertFromSelect(
                    $select,
                    $this->tableMaintainer->getMainTable(),
                    []
                )
            );
            $this->tableMaintainer->dropTableForDimensions($dimensions);

            $this->resourceConnection->getConnection()->commit();
        } catch (\Exception $e) {
            $this->resourceConnection->getConnection()->rollBack();
            throw $e;
        }
    }

    protected function buildIndex(array $dimensions, \Magento\Catalog\Model\ResourceModel\Product\Collection $products): void
    {
        $storeId = (int)$dimensions[\Magento\Store\Model\StoreDimensionProvider::DIMENSION_NAME]->getValue();
        $store = $this->storeManager->getStore($storeId);
        $generatedData = [];

        $this->batchReviewData->load($products->getColumnValues('entity_id'), $storeId);

        foreach ($products as $product) {
            $productData = $this->productDataProvider->generateProductData($product, $store);

            if (empty($productData)) {
                continue;
            }

            $generatedData[] = [
                'product_id' => (int)$product->getId(),
                'store_id' => (int)$store->getId(),
                'data' => $this->serializer->serialize($productData)
            ];
        }

        $this->batchReviewData->reset();
        $products->clear();

        if (empty($generatedData)) {
            return;
        }

        $this->resourceConnection->getConnection()->insertMultiple(
            $this->tableMaintainer->getMainTmpTable($dimensions),
            $generatedData
        );
    }
}
