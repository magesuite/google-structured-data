<?php
namespace MageSuite\GoogleStructuredData\Repository;

class ProductReviews
{

    protected $product = false;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    private $reviewFactory;
    /**
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    private $reviewCollectionFactory;

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
    public function getReviewsData()
    {
        $product = $this->getProduct();

        if (!$product) {
            return [];
        }

        $data = [];
        $storeMaganer = $this->storeManager->getStore()->getId();
        $reviews = $this->reviewFactory->create();

        $reviews->getEntitySummary($product, $storeMaganer);

        $ratingSummary = $product->getRatingSummary();

        if ($ratingSummary->getRatingSummary() && $ratingSummary->getReviewsCount()) {
            $ratingValue = $ratingSummary->getRatingSummary() ? ($ratingSummary->getRatingSummary() / 20): 0;
            $reviewCount = $ratingSummary->getReviewsCount();

            $data['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $ratingValue,
                'reviewCount' => $reviewCount
            ];
        }

        $reviewsCollection = $this->reviewCollectionFactory->create();

        $reviewsCollection->addStatusFilter(
            \Magento\Review\Model\Review::STATUS_APPROVED)
            ->addEntityFilter(
                'product',
                $product->getId()
            )->setDateOrder()
            ->setPageSize(10);

        $reviewData = [];
        foreach ($reviewsCollection as $review) {

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

    public function getProduct()
    {
        $product = $this->registry->registry('current_product');

        if(!$product){
            return false;
        }

        $this->product = $product;

        return $this->product;
    }
}