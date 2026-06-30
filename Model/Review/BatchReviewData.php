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
        $this->ensureLoaded($productId, $storeId);
        $key = $this->getKey($productId, $storeId);

        return $this->ratingSummaries[$key] ?? ['rating_value' => 0, 'review_count' => 0];
    }

    public function getReviews(int $productId, int $storeId): ?array
    {
        $this->ensureLoaded($productId, $storeId);
        $key = $this->getKey($productId, $storeId);

        return $this->reviews[$key] ?? [];
    }

    protected function ensureLoaded(int $productId, int $storeId): void
    {
        if (isset($this->loadedKeys[$this->getKey($productId, $storeId)])) {
            return;
        }

        $this->load([$productId], $storeId);
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
        $reviewIds = $this->getMostRecentReviewIds($productIds, $storeId);

        if (empty($reviewIds)) {
            return;
        }

        /** @var \Magento\Review\Model\ResourceModel\Review\Collection $collection */
        $collection = $this->reviewCollectionFactory->create();
        $collection->setDateOrder();

        $collection->getSelect()
            ->where('main_table.review_id IN (?)', $reviewIds)
            ->joinLeft(
                ['rov' => $collection->getTable('rating_option_vote')],
                'main_table.review_id = rov.review_id',
                ['percent']
            )
            ->group('main_table.review_id');

        foreach ($collection as $review) {
            $productId = (int)$review->getData('entity_pk_value');
            $this->reviews[$this->getKey($productId, $storeId)][] = $review;
        }
    }

    protected function getMostRecentReviewIds(array $productIds, int $storeId): array
    {
        $connection = $this->resourceConnection->getConnection();

        $rankedSelect = $connection->select()
            ->from(
                ['review' => $this->resourceConnection->getTableName('review')],
                [
                    'review_id',
                    'position' => new \Magento\Framework\DB\Sql\Expression(
                        'ROW_NUMBER() OVER (PARTITION BY review.entity_pk_value ORDER BY review.created_at DESC, review.review_id DESC)'
                    ),
                ]
            )
            ->join(
                ['review_store' => $this->resourceConnection->getTableName('review_store')],
                'review.review_id = review_store.review_id',
                []
            )
            ->where('review.entity_id = ?', $this->getProductReviewEntityId())
            ->where('review.entity_pk_value IN (?)', $productIds)
            ->where('review.status_id = ?', \Magento\Review\Model\Review::STATUS_APPROVED)
            ->where('review_store.store_id = ?', $storeId);

        $select = $connection->select()
            ->from(['ranked' => $rankedSelect], ['review_id'])
            ->where('ranked.position <= ?', self::MAX_REVIEWS_PER_PRODUCT);

        return $connection->fetchCol($select);
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
