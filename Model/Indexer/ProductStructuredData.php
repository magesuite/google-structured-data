<?php

namespace MageSuite\GoogleStructuredData\Model\Indexer;

class ProductStructuredData implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    public const INDEXER_ID = 'product_structured_data';

    protected \Magento\Catalog\Model\ResourceModel\Product $productResource;
    protected \MageSuite\GoogleStructuredData\Model\Indexer\ProductStructuredData\IndexBuilder $indexBuilder;
    protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $configuration;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \MageSuite\GoogleStructuredData\Model\Indexer\ProductStructuredData\IndexBuilder $indexBuilder,
        \MageSuite\GoogleStructuredData\Helper\Configuration\Product $configuration
    ) {
        $this->productResource = $productResource;
        $this->indexBuilder = $indexBuilder;
        $this->configuration = $configuration;
    }

    public function execute($ids): void
    {
        $this->executeList($ids);
    }

    public function executeFull(): void
    {
        if (!$this->configuration->isIndexingEnabled()) {
            return;
        }

        $ids = array_column(
            $this->productResource->getProductEntitiesInfo(['entity_id']),
            'entity_id'
        );

        $this->indexBuilder->reindexList($ids);
    }

    public function executeList(array $ids): void
    {
        if (!$this->configuration->isIndexingEnabled()) {
            return;
        }

        $this->indexBuilder->reindexList($ids);
    }

    public function executeRow($id): void
    {
        $this->executeList([$id]);
    }
}
