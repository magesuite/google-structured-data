<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Indexer\Product\Action;

class Rows implements \Magento\Framework\Indexer\DimensionalIndexerInterface
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Model\Indexer\Product\DataProvider $dataProvider,
        protected \MageSuite\GoogleStructuredData\Model\Indexer\Product\TableMaintainer $tableMaintainer,
        protected \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider,
        protected \Magento\Framework\Serialize\SerializerInterface $serializer,
        protected \Magento\Framework\App\ResourceConnection $resourceConnection,
        protected \Magento\Framework\Indexer\DimensionProviderInterface $dimensionProvider,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {}

    public function execute(array $entityIds): void
    {
        foreach ($this->dimensionProvider->getIterator() as $dimension) {
            $this->executeByDimensions($dimension, new \ArrayIterator($entityIds));
        }
    }

    public function executeByDimensions(array $dimensions, \Traversable $entityIds): void
    {
        $entityIds = iterator_to_array($entityIds);

        foreach (array_chunk($entityIds, $this->dataProvider->getBatchSize()) as $entityIdsChunk) {
            $collection = $this->dataProvider->getProducts($dimensions, $entityIdsChunk, 0);
            $this->prepareIndexTable($dimensions);
            $this->buildIndex($dimensions, $collection);
            $this->syncData($dimensions, $entityIds);
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
