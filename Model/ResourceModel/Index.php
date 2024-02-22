<?php

namespace MageSuite\GoogleStructuredData\Model\ResourceModel;

class Index
{
    public const INDEX_TABLE_NAME = 'products_structured_data_index';

    protected ?\Magento\Framework\DB\Adapter\AdapterInterface $connection;

    public function __construct(\Magento\Framework\App\ResourceConnection $resourceConnection)
    {
        $this->connection = $resourceConnection->getConnection();
    }

    public function startTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    public function rollBack(): void
    {
        $this->connection->rollBack();
    }

    public function commit(): void
    {
        $this->connection->commit();
    }

    public function insert(array $data): int
    {
        return $this->connection->insertMultiple(
            $this->connection->getTableName(self::INDEX_TABLE_NAME),
            $data
        );
    }

    public function deleteByProductId(array $toDeleteProductIds): void
    {
        $this->connection->delete(
            $this->connection->getTableName(self::INDEX_TABLE_NAME),
            $this->connection->quoteInto('product_id IN (?)', $toDeleteProductIds)
        );
    }

    public function getByProductIdsAndStoreId(array $productIds, int $storeId): ?array
    {
        $select = $this->connection
            ->select()
            ->from($this->connection->getTableName(self::INDEX_TABLE_NAME), ['product_id', 'data'])
            ->where('product_id IN (?)', $productIds)
            ->where('store_id = ?', $storeId);

        $result = [];

        $data = $this->connection->fetchPairs($select);

        foreach ($productIds as $productId) {
            $result[$productId] = $data[$productId] ?? '';
        }

        return $result;
    }
}
