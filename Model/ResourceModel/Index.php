<?php

namespace MageSuite\GoogleStructuredData\Model\ResourceModel;

class Index
{
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
            $this->connection->getTableName('products_structured_data_index'),
            $data
        );
    }

    public function deleteByProductId(array $toDeleteProductIds): void
    {
        $this->connection->delete(
            $this->connection->getTableName('products_structured_data_index'),
            $this->connection->quoteInto('product_id IN (?)', $toDeleteProductIds)
        );
    }

    public function getByProductIdsAndStoreId(array $productIds, int $storeId): ?array
    {
        $select = $this->connection->select();
        $select->from($this->connection->getTableName('products_structured_data_index'));
        $select->where('product_id IN (?)', $productIds);
        $select->where('store_id = ?', $storeId);

        $result = [];

        foreach ($this->connection->fetchAll($select) as $row) {
            $result[$row['product_id']] = $row['data'];
        }

        return $result;
    }
}
