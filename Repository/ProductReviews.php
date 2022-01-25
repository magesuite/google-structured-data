<?php

namespace MageSuite\GoogleStructuredData\Repository;

class ProductReviews
{
    const RATING_STARS = 5;

    protected $product = false;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    protected $reviewCollectionFactory;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory
    ) {
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->reviewFactory = $reviewFactory;
        $this->reviewCollectionFactory = $reviewCollectionFactory;
    }

    public function getRatingSummary($product)
    {
        $storeManager = $this->storeManager->getStore()->getId();
        $reviews = $this->reviewFactory->create();

        $reviews->getEntitySummary($product, $storeManager);

        $ratingSummary = $product->getRatingSummary();

        $ratingValue = $ratingSummary->getRatingSummary() ? ($ratingSummary->getRatingSummary() / (100 / self::RATING_STARS)) : 0;
        $reviewCount = $ratingSummary->getReviewsCount() ? $ratingSummary->getReviewsCount() : 0;

        return [
            'rating_value' => $ratingValue,
            'review_count' => $reviewCount
        ];
    }

    public function getReviews($product)
    {
        $reviewsCollection = $this->reviewCollectionFactory->create();

        $reviewsCollection
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->addEntityFilter('product', $product->getId())
            ->setDateOrder()
            ->setPageSize(10);

        return $reviewsCollection;
    }
}
