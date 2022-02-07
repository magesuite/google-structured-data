<?php

namespace MageSuite\GoogleStructuredData\Provider\Data;

class Product
{
    const IN_STOCK = 'InStock';
    const OUT_OF_STOCK = 'OutOfStock';

    const CACHE_KEY = 'google_structured_data_product_%s_%s';
    const CACHE_GROUP = 'google_structured_data_product';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \MageSuite\GoogleStructuredData\Provider\Data\Product\CompositeAttribute
     */
    protected $compositeAttributeDataProvider;

    /**
     * @var \MageSuite\GoogleStructuredData\Model\Review\GetProductReviews
     */
    protected $getProductReviews;

    /**
     * @var \MageSuite\GoogleStructuredData\Model\Review\GetProductRattingSummary
     */
    protected $getProductRattingSummary;

    /**
     * @var \MageSuite\GoogleStructuredData\Helper\Configuration\Product
     */
    protected $configuration;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Escaper $escaper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageSuite\GoogleStructuredData\Provider\Data\Product\CompositeAttribute $compositeAttributeDataProvider,
        \MageSuite\GoogleStructuredData\Model\Review\GetProductReviews $getProductReviews,
        \MageSuite\GoogleStructuredData\Model\Review\GetProductRattingSummary $getProductRattingSummary,
        \MageSuite\GoogleStructuredData\Helper\Configuration\Product $configuration
    ) {
        $this->timezone = $timezone;
        $this->serializer = $serializer;
        $this->cache = $cache;
        $this->escaper = $escaper;
        $this->storeManager = $storeManager;
        $this->compositeAttributeDataProvider = $compositeAttributeDataProvider;
        $this->getProductReviews = $getProductReviews;
        $this->getProductRattingSummary = $getProductRattingSummary;
        $this->configuration = $configuration;
    }

    public function getProductStructuredData(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $store = $this->storeManager->getStore();
        $cacheKey = $this->getCacheKey($product, $store);

        if (($cachedData = $this->cache->load($cacheKey)) != false) {
            return $this->serializer->unserialize($cachedData);
        }

        $productData = $this->getBaseProductData($product, $store);
        $offerData = $this->getOffers($product, $store);
        $reviewsData = $this->getReviewsData($product, $store);

        $result = array_merge($productData, $offerData, $reviewsData);

        $identities = $this->getIdentities($product);

        $this->cache->save(
            $this->serializer->serialize($result),
            $cacheKey,
            $identities
        );

        return $result;
    }

    public function getBaseProductData(\Magento\Catalog\Api\Data\ProductInterface $product, $store)
    {
        $structuredData = [
            '@context' => 'http://schema.org/',
            '@type' => 'Product',
            'name' => $this->escaper->escapeHtml($product->getName()),
            'image' => $this->getProductImages($product),
            'sku' => $this->escaper->escapeHtml($product->getSku()),
            'url' => $product->getProductUrl(),
            'itemCondition' => 'NewCondition'
        ];

        $attributeData = $this->compositeAttributeDataProvider->getAttributeData($product);

        return array_merge($structuredData, $attributeData);
    }

    public function getOffers(\Magento\Catalog\Api\Data\ProductInterface $product, $store)
    {
        $data = [];
        $currency = $store->getCurrentCurrencyCode();

        if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $simpleProducts = $product->getTypeInstance()->getUsedProducts($product);
            $productUrl = $product->getProductUrl();

            foreach ($simpleProducts as $simpleProduct) {
                $offer = $this->getOfferData($simpleProduct, $store, $currency);
                $offer['url'] = $productUrl;

                $data['offers'][] = $offer;
            }
        } else {
            $data['offers'] = $this->getOfferData($product, $store, $currency);
        }

        return $data;
    }

    public function getOfferData(\Magento\Catalog\Api\Data\ProductInterface $product, $store, $currency)
    {
        $productPrice = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();

        $data = [
            '@type' => 'Offer',
            'sku' => $this->escaper->escapeHtml($product->getSku()),
            'price' => number_format($productPrice, 2, '.', ''),
            'priceCurrency' => $currency,
            'availability' => $product->getIsSalable() ? self::IN_STOCK : self::OUT_OF_STOCK,
            'url' => $product->getProductUrl()
        ];

        $specialFromDate = $product->getSpecialFromDate();
        $specialToDate = $product->getSpecialToDate();
        $inRange = $this->timezone->isScopeDateInInterval($store, $specialFromDate, $specialToDate);

        if ($product->getSpecialPrice() && $specialToDate && $inRange) {
            $data['priceValidUntil'] = date('Y-m-d', strtotime($specialToDate));
        }

        return $data;
    }

    public function getReviewsData(\Magento\Catalog\Api\Data\ProductInterface $product, $store)
    {
        if (!$this->configuration->isShowRating()) {
            return [];
        }

        $data = [];
        $ratingSummary = $this->getProductRattingSummary->excute($product, $store->getId());

        if ($ratingSummary['rating_value'] && $ratingSummary['review_count']) {
            $data['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $ratingSummary['rating_value'],
                'reviewCount' => $ratingSummary['review_count']
            ];
        }

        $reviews = $this->getProductReviews->excute($product, $store->getId());
        $reviewData = [];

        foreach ($reviews as $review) {
            $row = [
                '@type' => 'Review',
                'author' => $this->escaper->escapeHtml($review->getNickname()),
                'datePublished' => $review->getCreatedAt(),
                'description' => $this->escaper->escapeHtml($review->getDetail()),
                'name' => $this->escaper->escapeHtml($review->getTitle())
            ];

            if ($percent = $review->getData('percent')) {
                $row['reviewRating'] = [
                    '@type' => 'Rating',
                    'bestRating' => 5,
                    'ratingValue' => ($percent / 20),
                    'worstRating' => 1
                ];
            }

            $reviewData[] = $row;
        }

        if (!empty($reviewData)) {
            $data['review'] = $reviewData;
        }

        return $data;
    }

    public function getProductImages(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $mediaGallery = $product->getMediaGalleryImages();
        if (!is_array($mediaGallery->getItems())) {
            return [];
        }

        $images = [];
        foreach ($mediaGallery as $image) {
            $images[] = $image->getUrl();
        }

        return $images;
    }

    protected function getCacheKey(\Magento\Catalog\Api\Data\ProductInterface $product, $store)
    {
        return sprintf(
            self::CACHE_KEY,
            $product->getId(),
            $store->getId()
        );
    }

    protected function getIdentities(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $identities = $product->getIdentities();
        $identities[] = self::CACHE_GROUP;

        $key = array_search(\Magento\Catalog\Model\Product::CACHE_TAG, $identities);

        if ($key == false) {
            return $identities;
        }

        unset($identities[$key]);

        return $identities;
    }
}
