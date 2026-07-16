<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Product;

class StructuredDataPreloader
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration,
        protected \MageSuite\GoogleStructuredData\Model\Product\AttributeList $attributeList,
        protected \MageSuite\GoogleStructuredData\Model\ProductStructuredDataIndexRepository $productStructuredDataIndexRepository,
        protected \MageSuite\GoogleStructuredData\Model\Catalog\BatchProductUrlData $batchProductUrlData
    ) {}

    public function preload(\Magento\Eav\Model\Entity\Collection\AbstractCollection $collection, int $storeId): void
    {
        $isIndexingEnabled = $this->productConfiguration->isIndexingEnabled();

        if (!$isIndexingEnabled) {
            $collection->addAttributeToSelect($this->attributeList->getList($storeId));
            $collection->_loadAttributes(); // phpcs:ignore
        }

        $productIds = $collection->getColumnValues('entity_id');
        $this->productStructuredDataIndexRepository->loadDataFromIndex($productIds, $storeId);

        $canonicalIds = $isIndexingEnabled
            ? $this->getIndexMissIds($productIds, $storeId)
            : $productIds;

        $this->batchProductUrlData->preloadCanonical($canonicalIds, $storeId);
    }

    protected function getIndexMissIds(array $productIds, int $storeId): array
    {
        return array_values(array_filter(
            $productIds,
            fn ($productId) => !$this->productStructuredDataIndexRepository->hasDataInIndex((int)$productId, $storeId)
        ));
    }
}
