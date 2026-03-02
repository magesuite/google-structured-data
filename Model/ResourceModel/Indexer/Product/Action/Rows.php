<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\ResourceModel\Indexer\Product\Action;

class Rows
{
    public function __construct(
        protected \Magento\Framework\App\ResourceConnection $resourceConnection,
        protected \Magento\Framework\EntityManager\MetadataPool $metadataPool
    ) {
    }

    public function getRelationsByChild(array $childrenIds): array
    {
        $connection = $this->resourceConnection->getConnection();

        $metadata = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $entityTable = $this->resourceConnection->getTableName($metadata->getEntityTable());
        $relationTable = $this->resourceConnection->getTableName('catalog_product_relation');
        $joinCondition = sprintf('relation.parent_id = entity.%s', $metadata->getLinkField());

        $select = $connection->select()
            ->from(['relation' => $relationTable], [])
            ->join(['entity' => $entityTable], $joinCondition, [$metadata->getIdentifierField()])
            ->where('child_id IN(?)', array_map('intval', $childrenIds));

        return $connection->fetchCol($select);
    }
}
