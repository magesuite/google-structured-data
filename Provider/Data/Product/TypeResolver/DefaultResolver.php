<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolver;

class DefaultResolver implements \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverInterface
{
    public const IN_STOCK = 'InStock';
    public const OUT_OF_STOCK = 'OutOfStock';

    public function __construct(
        protected \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        protected \Magento\Framework\Escaper $escaper,
        protected \MageSuite\GoogleStructuredData\Provider\Data\Product\CompositeAttribute $compositeAttributeDataProvider,
        protected \MageSuite\GoogleStructuredData\Model\Review\GetProductReviews $getProductReviews,
        protected \MageSuite\GoogleStructuredData\Model\Review\GetProductRattingSummary $getProductRattingSummary,
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration
    ) {}

    public function isApplicable(string $productTypeId): bool
    {
        return true;
    }

    public function execute(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store): array
    {
        return $this->getProductStructuredData($product, $store);
    }

    public function getProductStructuredData(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store): array
    {
        $productData = $this->getBaseProductData($product, $store);
        $offerData = $this->getOffers($product, $store);

        $reviewsData = $this->getReviewsData($product, $store);

        return array_merge($productData, $offerData, $reviewsData);
    }

    public function getBaseProductData(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store): array
    {
        $structuredData = [
            '@context' => 'https://schema.org/',
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

    public function getOffers(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store): array
    {
        $currency = $store->getCurrentCurrencyCode();

        return [
            'offers' => $this->getOfferData($product, $store, $currency)
        ];
    }

    public function getOfferData(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store, string $currency): array
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

    public function getReviewsData(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store): array
    {
        if (!$this->productConfiguration->shouldShowRating()) {
            return [];
        }

        $data = [];
        $ratingSummary = $this->getProductRattingSummary->execute($product, (int)$store->getId());

        if ($ratingSummary['rating_value'] && $ratingSummary['review_count']) {
            $data['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $ratingSummary['rating_value'],
                'reviewCount' => $ratingSummary['review_count']
            ];
        }

        $reviews = $this->getProductReviews->execute($product, (int)$store->getId());
        $reviewData = [];

        foreach ($reviews as $review) {
            $reviewData[] = $this->buildReviewData($review);
        }

        if (!empty($reviewData)) {
            $data['review'] = $reviewData;
        }

        return $data;
    }

    public function getProductImages(\Magento\Catalog\Api\Data\ProductInterface $product): array
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

    public function buildReviewData(\Magento\Review\Model\Review $review): array
    {
        $row = [
            '@type' => 'Review',
            'author' => ['@type' => 'Person', 'name' => $this->escaper->escapeHtml($review->getNickname())],
            'datePublished' => $review->getCreatedAt(),
            'description' => $this->escaper->escapeHtml($review->getDetail()),
            'name' => $this->escaper->escapeHtml($review->getTitle())
        ];

        $percent = $review->getData('percent');

        if ($percent) {
            $row['reviewRating'] = [
                '@type' => 'Rating',
                'bestRating' => 5,
                'ratingValue' => ($percent / 20),
                'worstRating' => 1
            ];
        }

        return $row;
    }
}
