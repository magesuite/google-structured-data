<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\ResourceModel\Product;

class InventoryData
{
    protected array $stockIdByWebsite = [];

    public function __construct(
        protected \Magento\InventorySalesApi\Api\StockResolverInterface $stockResolver,
        protected \Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
        protected \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {}

    public function addStockDataToCollection(\Magento\Catalog\Model\ResourceModel\Product\Collection $collection, int $storeId): void
    {
        $websiteId = $this->getWebsiteId($storeId);
        $stockId = $this->getStockId($websiteId);
        $tableName = $this->stockIndexTableNameResolver->execute($stockId);
        $collection->getSelect()->joinLeft(
            ['stock_index' => $tableName],
            'e.sku = stock_index.' . \Magento\InventoryIndexer\Indexer\IndexStructure::SKU,
            [
                'is_salable' => 'stock_index.' . \Magento\InventoryIndexer\Indexer\IndexStructure::IS_SALABLE
            ]
        );
    }

    public function addStockDataToProducts(array $products, int $storeId): void
    {
        $skus = array_map(function ($product) {
            return $product->getSku();
        }, $products);

        if (empty($skus))  {
            return;
        }
        
        $websiteId = $this->getWebsiteId($storeId);
        $stockId = $this->getStockId($websiteId);
        $tableName = $this->stockIndexTableNameResolver->execute($stockId);
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(['stock_index' => $tableName], [
                \Magento\InventoryIndexer\Indexer\IndexStructure::SKU,
                \Magento\InventoryIndexer\Indexer\IndexStructure::IS_SALABLE
            ])
            ->where('stock_index.' . \Magento\InventoryIndexer\Indexer\IndexStructure::SKU . ' IN (?)', $skus);
        $stockData = $connection->fetchAssoc($select);

        foreach ($products as &$product) {
            if (!isset($stockData[$product['sku']])) {
                continue;
            }

            $product->setData('is_salable', $stockData[$product['sku']][\Magento\InventoryIndexer\Indexer\IndexStructure::IS_SALABLE]);
        }
    }

    protected function getWebsiteId($storeId): int
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
