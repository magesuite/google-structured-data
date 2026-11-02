<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Product;

class StructuredDataPreloader
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration,
        protected \MageSuite\GoogleStructuredData\Model\Product\AttributeList $attributeList,
        protected \MageSuite\GoogleStructuredData\Model\ProductStructuredDataIndexRepository $productStructuredDataIndexRepository,
        protected \MageSuite\GoogleStructuredData\Model\Catalog\BatchProductUrlData $batchProductUrlData,
        protected \MageSuite\GoogleStructuredData\Model\CatalogRule\BatchRuleEndDateData $batchRuleEndDateData,
        protected \MageSuite\GoogleStructuredData\Model\ResourceModel\Product\InventoryData $inventoryData
    ) {}

    public function preload(\Magento\Catalog\Model\ResourceModel\Product\Collection $collection, int $storeId): void
    {
        $productIds = $collection->getColumnValues('entity_id');
        $this->productStructuredDataIndexRepository->loadDataFromIndex($productIds, $storeId);
        $this->preloadSalableStatuses($collection, $storeId);

        $generatedProductIds = $this->productConfiguration->isIndexingEnabled() ? $this->getIndexMissIds($productIds, $storeId) : $productIds;

        if (empty($generatedProductIds)) {
            return;
        }

        $collection->addAttributeToSelect($this->attributeList->getList($storeId));
        $collection->_loadAttributes(); // phpcs:ignore
        $collection->addMediaGalleryData();

        $this->batchProductUrlData->preloadCanonical($generatedProductIds, $storeId);
        $this->batchRuleEndDateData->load($generatedProductIds, $storeId);
    }

    public function preloadCanonicalUrls(\Magento\Eav\Model\Entity\Collection\AbstractCollection $collection, int $storeId): void
    {
        $this->batchProductUrlData->preloadCanonical($collection->getColumnValues('entity_id'), $storeId);
    }

    protected function getIndexMissIds(array $productIds, int $storeId): array
    {
        return array_values(array_filter($productIds, fn ($productId) => !$this->productStructuredDataIndexRepository->hasDataInIndex((int)$productId, $storeId)));
    }

    protected function preloadSalableStatuses(\Magento\Catalog\Model\ResourceModel\Product\Collection $collection, int $storeId): void
    {
        if (!$this->productConfiguration->isOfferAvailabilityRefreshEnabled($storeId)) {
            return;
        }

        $indexedSkus = array_map(fn ($productId): array => $this->getSkus($this->productStructuredDataIndexRepository->getDataFromIndex((int)$productId, $storeId)), $collection->getColumnValues('entity_id'));
        $this->inventoryData->preloadSalableStatuses(array_values(array_unique(array_merge($collection->getColumnValues('sku'), ...$indexedSkus))), $storeId);
    }

    protected function getSkus(array $productData): array
    {
        $skus = [];
        array_walk_recursive($productData, function (mixed $value, int|string $key) use (&$skus): void {
            if ($key === 'sku' && is_string($value)) {
                $skus[] = htmlspecialchars_decode($value, ENT_QUOTES | ENT_SUBSTITUTE);
            }
        });

        return $skus;
    }
}
