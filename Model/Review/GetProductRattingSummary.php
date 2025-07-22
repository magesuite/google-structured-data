<?php

namespace MageSuite\GoogleStructuredData\Model\Review;

class GetProductRattingSummary
{
    public const RATING_STARS = 5;

    public function __construct(
        protected \Magento\Review\Model\AppendSummaryData $appendSummaryData,
    ) {}

    public function execute($product, $storeId): array
    {
        $this->appendSummaryData->execute($product, $storeId, \Magento\Review\Model\Review::ENTITY_PRODUCT_CODE);
        $ratingSummary = $product->getRatingSummary();
        $reviewsCount = $product->getReviewsCount();

        $ratingValue = $ratingSummary ? ($ratingSummary / (100 / self::RATING_STARS)) : 0;
        $reviewCount = $reviewsCount ?: 0;

        return [
            'rating_value' => $ratingValue,
            'review_count' => $reviewCount
        ];
    }
}
