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
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var \MageSuite\GoogleStructuredData\Repository\ProductReviews
     */
    protected $productReviews;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \MageSuite\GoogleStructuredData\Repository\ProductReviews $productReviews,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->reviewFactory = $reviewFactory;
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->productRepository = $productRepository;
        $this->productReviews = $productReviews;
        $this->eventManager = $eventManager;
        $this->scopeConfig = $scopeConfig;
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

        $productData = $this->getBaseProductData($product);

        $offerData = $this->getOffers();

        $reviewsData = $this->getReviewsData();


        return array_merge($productData, $offerData, $reviewsData);
    }

    /**
     * @param $product \Magento\Catalog\Model\Product
     * @return array
     */
    public function getBaseProductData($product)
    {
        $structuredData = [
            "@context" => "http://schema.org/",
            "@type" => "Product",
            "name" => $product->getName(),
            "image" => $this->getProductImages($product),
            "sku" => $product->getSku(),
            "url" => $product->getProductUrl(),
            "condition" => "New"
        ];

        if($description = $this->getAttributeValue($product, 'description')){
            $structuredData['description'] = $description;
        }

        if($brand = $this->getAttributeValue($product, 'brand')) {
            $structuredData['brand'] = $brand;
        }

        if($manufacturer = $this->getAttributeValue($product, 'manufacturer')){
            $structuredData['manufacturer'] = $manufacturer;
        }

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

    public function getOffers()
    {
        $product = $this->getProduct();

        if (!$product) {
           return [];
        }

        $data = [];
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

    public function getReviewsData()
    {
        $config = $this->getConfiguration();

        if(!$config['show_rating']){
            return [];
        }
        $product = $this->getProduct();

        if (!$product) {
            return [];
        }

        $data = [];

        $ratingSummary = $this->productReviews->getRatingSummary($product);

        if ($ratingSummary->getRatingSummary() && $ratingSummary->getReviewsCount()) {
            $ratingValue = $ratingSummary->getRatingSummary() ? ($ratingSummary->getRatingSummary() / 20): 0;
            $reviewCount = $ratingSummary->getReviewsCount();

            $data['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $ratingValue,
                'reviewCount' => $reviewCount
            ];
        }

        $reviews = $this->productReviews->getReviews($product);

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



    public function getAttributeValue($product, $type)
    {
        $config = $this->getConfiguration();

        if(!isset($config[$type])){
            return '';
        }

        return $product->getAttributeText($config[$type]);
    }

    public function getConfiguration()
    {
        return $this->scopeConfig->getValue('structured_data/product_page');
    }
}