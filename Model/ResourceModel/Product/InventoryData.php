<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\ResourceModel\Product;

class InventoryData
{
    protected array $stockIdByWebsite = [];
    protected array $salableStatuses = [];

    public function __construct(
        protected \Magento\InventorySalesApi\Api\StockResolverInterface $stockResolver,
        protected \Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
        protected \Magento\Framework\App\ResourceConnection $resourceConnection,
        protected int $batchSize = 1000
    ) {}

    public function addStockDataToProducts(array $products, int $storeId): void
    {
        $skus = array_map(function ($product) {
            return $product->getSku();
        }, $products);

        if (empty($skus)) {
            return;
        }

        $stockData = $this->fetchStockData($this->getStockIndexTableName($storeId), $skus);

        foreach ($products as &$product) {
            if (!isset($stockData[$product['sku']])) {
                continue;
            }

            $product->setData('is_salable', $stockData[$product['sku']][\Magento\InventoryIndexer\Indexer\IndexStructure::IS_SALABLE]);
        }
    }

    public function getSalableStatusesBySku(array $skus, int $storeId): array
    {
        if (empty($skus)) {
            return [];
        }

        $this->preloadSalableStatuses($skus, $storeId);

        return array_filter(array_intersect_key($this->salableStatuses[$storeId], array_flip($skus)), static fn (?bool $isSalable): bool => $isSalable !== null);
    }

    public function preloadSalableStatuses(array $skus, int $storeId): void
    {
        $notLoadedSkus = array_values(array_diff($skus, array_keys($this->salableStatuses[$storeId] ?? [])));

        if (empty($notLoadedSkus)) {
            return;
        }

        $stockData = $this->fetchStockData($this->getStockIndexTableName($storeId), $notLoadedSkus);
        $salableStatuses = array_map(static fn (array $stockItem): bool => (bool)$stockItem[\Magento\InventoryIndexer\Indexer\IndexStructure::IS_SALABLE], $stockData);
        $this->salableStatuses[$storeId] = array_replace($this->salableStatuses[$storeId] ?? [], array_fill_keys($notLoadedSkus, null), $salableStatuses);
    }

    protected function getStockIndexTableName(int $storeId): string
    {
        return $this->stockIndexTableNameResolver->execute($this->getStockId($this->getWebsiteId($storeId)));
    }

    protected function fetchStockData(string $tableName, array $skus): array
    {
        $connection = $this->resourceConnection->getConnection();
        $stockData = [];

        foreach (array_chunk($skus, $this->batchSize) as $skuChunk) {
            $select = $connection->select()
                ->from(['stock_index' => $tableName], [
                    \Magento\InventoryIndexer\Indexer\IndexStructure::SKU,
                    \Magento\InventoryIndexer\Indexer\IndexStructure::IS_SALABLE
                ])
                ->where('stock_index.' . \Magento\InventoryIndexer\Indexer\IndexStructure::SKU . ' IN (?)', $skuChunk);
            $stockData += $connection->fetchAssoc($select);
        }

        return $stockData;
    }

    protected function getWebsiteId(int $storeId): int
    {
        return (int)$this->storeManager->getStore($storeId)->getWebsiteId();
    }

    protected function getStockId(int $websiteId): int
    {
        if (!isset($this->stockIdByWebsite[$websiteId])) {
            $websiteCode = $this->storeManager->getWebsite($websiteId)->getCode();
            $stock = $this->stockResolver->execute(
                \Magento\InventorySalesApi\Api\Data\SalesChannelInterface::TYPE_WEBSITE,
                $websiteCode
            );
            $this->stockIdByWebsite[$websiteId] = (int)$stock->getStockId();
        }

        return $this->stockIdByWebsite[$websiteId];
    }
}
