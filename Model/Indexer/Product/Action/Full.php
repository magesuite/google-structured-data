<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Indexer\Product\Action;

class Full implements \Magento\Framework\Indexer\DimensionalIndexerInterface
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Model\Indexer\Product\DataProvider $dataProvider,
        protected \MageSuite\GoogleStructuredData\Model\Indexer\Product\TableMaintainer $tableMaintainer,
        protected \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider,
        protected \Magento\Framework\Serialize\SerializerInterface $serializer,
        protected \Magento\Framework\App\ResourceConnection $resourceConnection,
        protected \Magento\Framework\Indexer\DimensionProviderInterface $dimensionProvider,
        protected \Magento\Indexer\Model\ProcessManager $processManager,
        protected \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher $activeTableSwitcher,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {}

    public function execute(): self
    {
        $this->clearReplicaTable();
        $this->reindex();
        $this->switchTables();

        return $this;
    }

    protected function clearReplicaTable(): void
    {
        $replicaTable = $this->tableMaintainer->getMainReplicaTable();
        $this->tableMaintainer->cleanTable($replicaTable);
    }

    public function reindex(): void
    {
        $userFunctions = [];

        foreach ($this->dimensionProvider->getIterator() as $dimension) {
            $userFunctions[] = function () use ($dimension) {
                $this->executeByDimensions($dimension);
            };
        }

        $this->processManager->execute($userFunctions);
    }

    public function executeByDimensions(array $dimensions, ?\Traversable $entityIds = null): void
    {
        $lastProductId = 0;

        while (true) {
            $collection = $this->dataProvider->getProducts($dimensions, null, $lastProductId);

            if ($collection->count() === 0) {
                break;
            }

            $lastProductId = (int)$collection->getLastItem()->getId();
            $this->buildIndex($dimensions, $collection);
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
                'store_id' => $storeId,
                'data' => $this->serializer->serialize($productData)
            ];
        }

        $products->clear();

        if (empty($generatedData)) {
            return;
        }

        $this->resourceConnection->getConnection()->insertMultiple(
            $this->tableMaintainer->getMainReplicaTable(),
            $generatedData
        );
    }

    protected function switchTables(): void
    {
        $connection = $this->resourceConnection->getConnection();
        $this->activeTableSwitcher->switchTable(
            $connection,
            [$this->tableMaintainer->getMainTable()]
        );
    }
}
