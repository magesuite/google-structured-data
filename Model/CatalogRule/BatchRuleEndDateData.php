<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\CatalogRule;

class BatchRuleEndDateData
{
    protected array $endDates = [];

    protected array $loadedKeys = [];

    public function __construct(
        protected \Magento\Framework\App\ResourceConnection $resourceConnection,
        protected \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {}

    public function load(array $productIds, int $storeId): void
    {
        $productIds = $this->getNotLoadedProductIds($productIds, $storeId);

        if (empty($productIds)) {
            return;
        }

        foreach ($productIds as $productId) {
            $this->loadedKeys[$this->getKey($productId, $storeId)] = true;
        }

        foreach ($this->fetchEndDates($productIds, $storeId) as $productId => $endDate) {
            $this->endDates[$this->getKey((int)$productId, $storeId)] = $endDate;
        }
    }

    public function getEndDate(int $productId, int $storeId): ?string
    {
        $this->load([$productId], $storeId);

        return $this->endDates[$this->getKey($productId, $storeId)] ?? null;
    }

    public function reset(): void
    {
        $this->endDates = [];
        $this->loadedKeys = [];
    }

    protected function getNotLoadedProductIds(array $productIds, int $storeId): array
    {
        $productIds = array_unique(array_map('intval', $productIds));

        return array_values(array_filter($productIds, fn (int $productId): bool => !isset($this->loadedKeys[$this->getKey($productId, $storeId)])));
    }

    protected function fetchEndDates(array $productIds, int $storeId): array
    {
        $store = $this->storeManager->getStore($storeId);
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName('catalogrule_product_price'), ['product_id', 'earliest_end_date'])
            ->where('product_id IN (?)', $productIds)
            ->where('website_id = ?', (int)$store->getWebsiteId())
            ->where('customer_group_id = ?', \Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID)
            ->where('rule_date = ?', $this->timezone->scopeDate($store)->format('Y-m-d'))
            ->where('earliest_end_date IS NOT NULL');

        return array_map(fn (string $utcEndDate): string => $this->getWebsiteDate($utcEndDate, $store), $connection->fetchPairs($select));
    }

    protected function getWebsiteDate(string $utcDate, \Magento\Store\Api\Data\StoreInterface $store): string
    {
        return $this->timezone->scopeDate($store, strtotime(sprintf('%s UTC', $utcDate)))->format('Y-m-d');
    }

    protected function getKey(int $productId, int $storeId): string
    {
        return sprintf('%s_%s', $productId, $storeId);
    }
}
