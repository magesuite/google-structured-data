<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Indexer\Product;

class TableMaintainer extends \Magento\Indexer\Model\ResourceModel\AbstractResource
{
    public const MAIN_INDEX_TABLE = 'product_structured_data_index';

    protected $tmpTableSuffix = '_temp';
    protected $additionalTableSuffix = '_replica';
    protected array $mainTmpTable = [];

    protected function _construct()
    {
        $this->_init(self::MAIN_INDEX_TABLE, 'entity_id');
    }

    public function getMainReplicaTable(): string
    {
        return $this->getMainTable() . $this->additionalTableSuffix;
    }

    public function truncateTable(string $tableName): void
    {
        if ($this->getConnection()->isTableExists($tableName)) {
            $this->getConnection()->truncateTable($tableName);
        }
    }

    public function cleanTable(string $tableName): void
    {
        $this->getConnection()->delete($tableName);
    }

    public function getMainTableByDimensions(array $dimensions): string
    {
        $tableName = $this->getTable(self::MAIN_INDEX_TABLE);

        foreach ($dimensions as $dimension) {
            $tableName .= '_' . $dimension->getValue();
        }

        return $tableName;
    }

    public function createMainTmpTable(array $dimensions): void
    {
        $templateTableName = $this->_resources->getTableName(self::MAIN_INDEX_TABLE);
        $temporaryTableName = $this->getMainTableByDimensions($dimensions) . $this->tmpTableSuffix;
        $this->getConnection()->createTemporaryTableLike($temporaryTableName, $templateTableName, true);
        $this->mainTmpTable[$this->getArrayKeyForTmpTable($dimensions)] = $temporaryTableName;
    }

    public function getMainTmpTable(array $dimensions): string
    {
        $cacheKey = $this->getArrayKeyForTmpTable($dimensions);
        if (!isset($this->mainTmpTable[$cacheKey])) {
            throw new \LogicException(
                sprintf('Temporary table for provided dimensions "%s" does not exist', $cacheKey)
            );
        }
        return $this->mainTmpTable[$cacheKey];
    }

    private function getArrayKeyForTmpTable(array $dimensions): string
    {
        $key = $this->tmpTableSuffix;

        foreach ($dimensions as $dimension) {
            $key .= $dimension->getName() . '_' . $dimension->getValue();
        }

        return $key;
    }

    public function dropTableForDimensions(array $dimensions): void
    {
        $mainTableName = $this->getMainTmpTable($dimensions);
        $this->getConnection()->dropTable($mainTableName);
    }
}
