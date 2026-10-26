<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Indexer;

class Product implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $configuration,
        protected \MageSuite\GoogleStructuredData\Model\Indexer\Product\Action\Full $fullAction,
        protected \MageSuite\GoogleStructuredData\Model\Indexer\Product\Action\Rows $rowsAction,
        protected \MageSuite\GoogleStructuredData\Model\Indexer\Product\TableMaintainer $tableMaintainer,
        protected \Magento\Framework\Indexer\CacheContext $cacheContext
    ) {}

    public function execute(mixed $ids): void
    {
        if (!$this->configuration->isIndexingEnabled()) {
            return;
        }

        $this->rowsAction->execute($ids);
        $this->cacheContext->registerEntities(\Magento\Catalog\Model\Product::CACHE_TAG, $ids);
    }

    public function executeFull(): void
    {
        if (!$this->configuration->isIndexingEnabled()) {
            return;
        }

        $this->fullAction->execute();
        $changedProductIds = $this->tableMaintainer->getChangedProductIds();

        if (empty($changedProductIds)) {
            return;
        }

        $this->cacheContext->registerEntities(\Magento\Catalog\Model\Product::CACHE_TAG, $changedProductIds);
    }

    public function executeList(array $ids): void
    {
        $this->execute($ids);
    }

    public function executeRow(mixed $id): void
    {
        $this->execute([$id]);
    }
}
