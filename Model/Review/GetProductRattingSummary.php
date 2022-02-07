<?php

namespace MageSuite\GoogleStructuredData\Model\Review;

class GetProductRattingSummary
{
    const RATING_STARS = 5;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    public function __construct(\Magento\Review\Model\ReviewFactory $reviewFactory)
    {
        $this->reviewFactory = $reviewFactory;
    }

    public function excute($product, $storeId)
    {
        $reviews = $this->reviewFactory->create();

        $reviews->getEntitySummary($product, $storeId);
        $ratingSummary = $product->getRatingSummary();

        $ratingValue = $ratingSummary->getRatingSummary() ? ($ratingSummary->getRatingSummary() / (100 / self::RATING_STARS)) : 0;
        $reviewCount = $ratingSummary->getReviewsCount() ? $ratingSummary->getReviewsCount() : 0;

        return [
            'rating_value' => $ratingValue,
            'review_count' => $reviewCount
        ];
    }
}
