<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolver;

class DefaultResolver implements \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverInterface
{
    public const IN_STOCK = 'InStock';
    public const OUT_OF_STOCK = 'OutOfStock';

    protected array $cachedOfferData = [];
    protected array $cachedProductData = [];

    public function __construct(
        protected \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        protected \Magento\Framework\Escaper $escaper,
        protected \Magento\Framework\Filter\StripTags $stripTagsFilter,
        protected \MageSuite\GoogleStructuredData\Provider\Data\Product\CompositeAttribute $compositeAttributeDataProvider,
        protected \MageSuite\GoogleStructuredData\Model\Review\GetProductReviews $getProductReviews,
        protected \MageSuite\GoogleStructuredData\Model\Review\GetProductRattingSummary $getProductRattingSummary,
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration,
        protected \MageSuite\GoogleStructuredData\Model\ResourceModel\Product\InventoryData $inventoryData,
        protected \MageSuite\GoogleStructuredData\Model\Audience\SuggestedGenderResolver $suggestedGenderResolver,
        protected \MageSuite\GoogleStructuredData\Model\Audience\SuggestedMinAgeResolver $suggestedMinAgeResolver
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
        $audienceData = $this->getAudienceData($product, $store);

        return array_merge($productData, $offerData, $reviewsData, $audienceData);
    }

    public function getBaseProductData(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store): array
    {
        $cacheKey = sprintf('%d_%d', $product->getId(), $store->getId());

        if (isset($this->cachedProductData[$cacheKey])) {
            return $this->cachedProductData[$cacheKey];
        }

        $structuredData = [
            '@context' => 'https://schema.org/',
            '@type' => 'Product',
            'name' => $this->escaper->escapeHtml($product->getName()),
            'image' => $this->getProductImages($product, $store),
            'sku' => $this->escaper->escapeHtml($product->getSku()),
            'url' => $product->getProductUrl(),
            'itemCondition' => 'NewCondition'
        ];

        $attributeData = $this->compositeAttributeDataProvider->getAttributeData($product, (int)$store->getId());
        $this->cachedProductData[$cacheKey] = array_merge($structuredData, $attributeData);

        return $this->cachedProductData[$cacheKey];
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
        $cacheKey = sprintf('%d_%d_%s', $product->getId(), $store->getId(), $currency);

        if (isset($this->cachedOfferData[$cacheKey])) {
            return $this->cachedOfferData[$cacheKey];
        }

        $productPrice = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        $data = [
            '@type' => 'Offer',
            'sku' => $this->escaper->escapeHtml($product->getSku()),
            'price' => number_format($productPrice, 2, '.', ''),
            'priceCurrency' => $currency,
            'availability' => $product->isAvailable() ? self::IN_STOCK : self::OUT_OF_STOCK,
            'url' => $product->getProductUrl()
        ];
        $specialFromDate = $product->getSpecialFromDate();
        $specialToDate = $product->getSpecialToDate();
        $inRange = $this->timezone->isScopeDateInInterval($store, $specialFromDate, $specialToDate);

        if ($product->getSpecialPrice() && $specialToDate && $inRange) {
            $data['priceValidUntil'] = date('Y-m-d', strtotime($specialToDate));
        }

        $this->cachedOfferData[$cacheKey] = $data;

        return $this->cachedOfferData[$cacheKey];
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
            $reviewData[] = $this->buildReviewData($review, $store);
        }

        if (!empty($reviewData)) {
            $data['review'] = $reviewData;
        }

        return $data;
    }

    public function getProductImages(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        \Magento\Store\Api\Data\StoreInterface $store
    ): array {
        $mediaGallery = $product->getMediaGallery('images');
        if (!is_array($mediaGallery) || empty($mediaGallery)) {
            return [];
        }

        $baseMediaUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product';
        $images = [];

        foreach ($mediaGallery as $image) {
            if (!empty($image['disabled'])) {
                continue;
            }

            $file = $image['file'] ?? '';
            if ($file === '') {
                continue;
            }

            $images[] = $baseMediaUrl . $file;
        }

        return $images;
    }

    public function buildReviewData(\Magento\Review\Model\Review $review, \Magento\Store\Api\Data\StoreInterface $store): array
    {
        $row = [
            '@type' => 'Review',
            'author' => ['@type' => 'Person', 'name' => $this->escaper->escapeHtml($review->getNickname())],
            'datePublished' => $this->timezone->scopeDate($store, $review->getCreatedAt(), true)->format(\DateTime::ATOM),
            'description' => $this->stripTagsFilter->filter($review->getDetail()),
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

    protected function getDescription(\Magento\Catalog\Api\Data\ProductInterface $product, int $storeId): string
    {
        $attributeCode = $this->productConfiguration->getConfiguredAttribute('description', $storeId);
        $description = $product->getData($attributeCode) ?: $product->getData('description');

        return $this->stripTagsFilter->filter((string)$description);
    }

    public function getAudienceData(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        \Magento\Store\Api\Data\StoreInterface $store
    ): array
    {
        if (!$this->productConfiguration->isAudienceEnabled((int)$store->getId())) {
            return [];
        }

        $suggestedGender = $this->suggestedGenderResolver->resolve($product, (int)$store->getId());
        $suggestedMinAge = $this->suggestedMinAgeResolver->resolve($product, (int)$store->getId());

        if (empty($suggestedGender) && empty($suggestedMinAge)) {
            return [];
        }

        return [
            'audience' => [
                '@type' => 'PeopleAudience',
                'suggestedGender' => $suggestedGender,
                'suggestedMinAge' => $suggestedMinAge
            ]
        ];
    }

}
