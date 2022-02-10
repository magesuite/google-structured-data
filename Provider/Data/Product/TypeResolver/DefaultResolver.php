<?php

namespace MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolver;

class DefaultResolver implements \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverInterface
{
    const IN_STOCK = 'InStock';
    const OUT_OF_STOCK = 'OutOfStock';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

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
        \Magento\Framework\Escaper $escaper,
        \MageSuite\GoogleStructuredData\Provider\Data\Product\CompositeAttribute $compositeAttributeDataProvider,
        \MageSuite\GoogleStructuredData\Model\Review\GetProductReviews $getProductReviews,
        \MageSuite\GoogleStructuredData\Model\Review\GetProductRattingSummary $getProductRattingSummary,
        \MageSuite\GoogleStructuredData\Helper\Configuration\Product $configuration
    ) {
        $this->timezone = $timezone;
        $this->escaper = $escaper;
        $this->compositeAttributeDataProvider = $compositeAttributeDataProvider;
        $this->getProductReviews = $getProductReviews;
        $this->getProductRattingSummary = $getProductRattingSummary;
        $this->configuration = $configuration;
    }

    public function isApplicable($productTypeId)
    {
        return true;
    }

    public function execute(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store, bool $withReviews = true)
    {
        return $this->getProductStructuredData($product, $store, $withReviews);
    }

    public function getProductStructuredData(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store, bool $withReviews = true)
    {
        $productData = $this->getBaseProductData($product, $store);
        $offerData = $this->getOffers($product, $store);

        $reviewsData = $withReviews ? $this->getReviewsData($product, $store) : [];

        return array_merge($productData, $offerData, $reviewsData);
    }

    public function getBaseProductData(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store)
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
        $currency = $store->getCurrentCurrencyCode();

        return [
            'offers' => $this->getOfferData($product, $store, $currency)
        ];
    }

    public function getOfferData(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store, $currency)
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

    public function getReviewsData(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store)
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
}
