<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Review;

class BatchReviewData
{
    protected const int MAX_REVIEWS_PER_PRODUCT = 10;

    protected const int RATING_STARS = 5;

    protected ?int $productReviewEntityId = null;

    protected array $ratingSummaries = [];

    protected array $reviews = [];

    protected array $loadedKeys = [];

    public function __construct(
        protected \Magento\Framework\App\ResourceConnection $resourceConnection,
        protected \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory,
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration
    ) {}

    public function load(array $productIds, int $storeId): void
    {
        if (!$this->productConfiguration->shouldShowRating()) {
            return;
        }

        $productIds = array_values(array_unique(array_map('intval', $productIds)));

        if (empty($productIds)) {
            return;
        }

        foreach ($productIds as $productId) {
            $this->loadedKeys[$this->getKey($productId, $storeId)] = true;
        }

        $this->loadRatingSummaries($productIds, $storeId);
        $this->loadReviews($productIds, $storeId);
    }

    public function getRatingSummary(int $productId, int $storeId): ?array
    {
        $key = $this->getKey($productId, $storeId);

        return $this->ratingSummaries[$key] ?? ['rating_value' => 0, 'review_count' => 0];
    }

    public function getReviews(int $productId, int $storeId): ?array
    {
        $key = $this->getKey($productId, $storeId);

        return $this->reviews[$key] ?? [];
    }

    public function reset(): void
    {
        $this->ratingSummaries = [];
        $this->reviews = [];
        $this->loadedKeys = [];
    }

    protected function loadRatingSummaries(array $productIds, int $storeId): void
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(
                $this->resourceConnection->getTableName('review_entity_summary'),
                ['entity_pk_value', 'reviews_count', 'rating_summary']
            )
            ->where('entity_type = ?', $this->getProductReviewEntityId())
            ->where('store_id = ?', $storeId)
            ->where('entity_pk_value IN (?)', $productIds);

        foreach ($connection->fetchAll($select) as $row) {
            $productId = (int)$row['entity_pk_value'];
            $ratingSummary = (int)$row['rating_summary'];
            $reviewsCount = (int)$row['reviews_count'];

            $this->ratingSummaries[$this->getKey($productId, $storeId)] = [
                'rating_value' => $ratingSummary ? ($ratingSummary / (100 / self::RATING_STARS)) : 0,
                'review_count' => $reviewsCount,
            ];
        }
    }

    protected function loadReviews(array $productIds, int $storeId): void
    {
        /** @var \Magento\Review\Model\ResourceModel\Review\Collection $collection */
        $collection = $this->reviewCollectionFactory->create();
        $collection
            ->addStoreFilter($storeId)
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->setDateOrder();

        $collection->getSelect()
            ->where('main_table.entity_id = ?', $this->getProductReviewEntityId())
            ->where('main_table.entity_pk_value IN (?)', $productIds)
            ->joinLeft(
                ['rov' => $collection->getTable('rating_option_vote')],
                'main_table.review_id = rov.review_id',
                ['percent']
            )
            ->group('main_table.review_id');

        foreach ($collection as $review) {
            $productId = (int)$review->getData('entity_pk_value');
            $key = $this->getKey($productId, $storeId);

            if (count($this->reviews[$key] ?? []) >= self::MAX_REVIEWS_PER_PRODUCT) {
                continue;
            }

            $this->reviews[$key][] = $review;
        }
    }

    protected function getProductReviewEntityId(): int
    {
        if ($this->productReviewEntityId !== null) {
            return $this->productReviewEntityId;
        }

        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName('review_entity'), ['entity_id'])
            ->where('entity_code = ?', \Magento\Review\Model\Review::ENTITY_PRODUCT_CODE);

        $this->productReviewEntityId = (int)$connection->fetchOne($select);

        return $this->productReviewEntityId;
    }

    protected function getKey(int $productId, int $storeId): string
    {
        return sprintf('%s_%s', $productId, $storeId);
    }
}
