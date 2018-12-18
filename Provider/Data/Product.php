<?php
namespace MageSuite\GoogleStructuredData\Provider\Data;

class Product
{
    const IN_STOCK = 'InStock';
    const OUT_OF_STOCK = 'OutOfStock';
    const TYPE_CONFIGURABLE = 'configurable';

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
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    )
    {
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->reviewFactory = $reviewFactory;
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->productRepository = $productRepository;
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

    public function getProductStructuredData($product = null)
    {
        if(!$product) {
            $product = $this->getProduct();
        }

        if (!$product) {
            return [];
        }

        $product = $this->productRepository->get($product->getSku());

        $data = $this->addBaseProductData($product);

        $data = $this->addOfferData($data);

        $data = $this->addReviewsData($data);


        return $data;
    }

    /**
     * @param $product \Magento\Catalog\Model\Product
     * @return array
     */
    public function addBaseProductData($product)
    {
        $structuredData = [
            "@context" => "http://schema.org/",
            "@type" => "Product",
            "name" => $product->getName(),
            "image" => $this->getProductImages($product),
            "description" => $product->getDescription(),
            "sku" => $product->getSku(),
            "url" => $product->getProductUrl()
        ];

        return $structuredData;
    }

    /**
     * @param $product \Magento\Catalog\Model\Product
     * @return array
     */
    public function getProductImages($product)
    {
        $mediaGallery = $product->getMediaGalleryImages();

        $images = [];

        foreach ($mediaGallery as $image) {
            $images[] = $image->getUrl();
        }

        return $images;
    }

    public function addOfferData($data)
    {
        $product = $this->getProduct();

        if (!$product) {
           return $data;
        }

        $currency = $this->storeManager->getStore()->getCurrentCurrencyCode();
        if ($product->getTypeId() == self::TYPE_CONFIGURABLE) {
            $simpleProducts = $product->getTypeInstance()->getUsedProducts($product);
            foreach ($simpleProducts as $simpleProduct) {
                $data['offers'][] = $this->getOfferData($simpleProduct, $currency);
            }
        } else {
            $data['offers'] = $this->getOfferData($product, $currency);
        }

        return $data;
    }

    public function getOfferData($product, $currency)
    {
        return [
            "@type" => "Offer",
            "sku" => $product->getSku(),
            "price" => number_format($this->getProductPrice($product), 2),
            "priceCurrency" => $currency,
            "availability" => $product->getIsSalable() ? self::IN_STOCK : self::OUT_OF_STOCK,
            "url" => $product->getProductUrl()
        ];
    }

    public function getProductPrice($product)
    {
        return $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
    }

    public function addReviewsData($data)
    {
        $product = $this->getProduct();

        if (!$product) {
            return $data;
        }

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
}