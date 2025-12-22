<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\ResourceModel;

class Index
{
    public const INDEX_TABLE_NAME = 'product_structured_data_index';

    protected \Magento\Framework\DB\Adapter\AdapterInterface $connection;

    public function __construct(
        protected \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->connection = $resourceConnection->getConnection();
    }

    public function getMainTable(): string
    {
        return $this->connection->getTableName(self::INDEX_TABLE_NAME);
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
            $this->getMainTable(),
            $data
        );
    }

    public function deleteByProductId(array $productIds, int $storeId): void
    {
        $where = [
            'store_id = ?' => $storeId,
            'product_id IN (?)' => $productIds,
        ];
        $this->connection->delete(
            $this->getMainTable(),
            $where
        );
    }

    public function getByProductIdsAndStoreId(array $productIds, int $storeId): array
    {
        $select = $this->connection
            ->select()
            ->from($this->getMainTable(), ['product_id', 'data'])
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
