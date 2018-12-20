<?php
namespace MageSuite\GoogleStructuredData\Repository;

class ProductReviews
{

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
    )
    {
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->reviewFactory = $reviewFactory;
        $this->reviewCollectionFactory = $reviewCollectionFactory;
    }

    public function getRatingSummary($product)
    {
        $storeMaganer = $this->storeManager->getStore()->getId();
        $reviews = $this->reviewFactory->create();

        $reviews->getEntitySummary($product, $storeMaganer);

        return $product->getRatingSummary();
    }

    public function getReviews($product)
    {
        $reviewsCollection = $this->reviewCollectionFactory->create();

        $reviewsCollection->addStatusFilter(
            \Magento\Review\Model\Review::STATUS_APPROVED)
            ->addEntityFilter(
                'product',
                $product->getId()
            )->setDateOrder()
            ->setPageSize(10);

        return $reviewsCollection;
    }

    public function getReviewsData()
    {
        $product = $this->getProduct();

        if (!$product) {
            return [];
        }

        $data = [];

        $ratingSummary = $this->getRatingSummary($product);

        if ($ratingSummary->getRatingSummary() && $ratingSummary->getReviewsCount()) {
            $ratingValue = $ratingSummary->getRatingSummary() ? ($ratingSummary->getRatingSummary() / 20): 0;
            $reviewCount = $ratingSummary->getReviewsCount();

            $data['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $ratingValue,
                'reviewCount' => $reviewCount
            ];
        }

        $reviews = $this->getReviews($product);

        $reviewData = [];
        foreach ($reviews as $review) {

            $reviewData[] = [
                "@type" => "Review",
                "author" => $review->getNickname(),
                "datePublished" => $review->getCreatedAt(),
                "description" => $review->getDetail(),
                "name" => $review->getTitle()
            ];
        }

        if(!empty($reviewData)) {
            $data['review'] = $reviewData;
        }

        return $data;
    }
}