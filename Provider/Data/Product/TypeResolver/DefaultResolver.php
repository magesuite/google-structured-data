<?php

namespace MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolver;

class DefaultResolver implements \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverInterface
{
    const IN_STOCK = 'InStock';
    const OUT_OF_STOCK = 'OutOfStock';

    protected \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone;

    protected \Magento\Framework\Escaper $escaper;

    protected \MageSuite\GoogleStructuredData\Provider\Data\Product\CompositeAttribute $compositeAttributeDataProvider;

    protected \MageSuite\GoogleStructuredData\Model\Review\GetProductReviews $getProductReviews;

    protected \MageSuite\GoogleStructuredData\Model\Review\GetProductRattingSummary $getProductRattingSummary;

    protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration;

    protected \MageSuite\GoogleStructuredData\Helper\Configuration $configuration;

    protected \MageSuite\GoogleStructuredData\Provider\Data\Product\DeliveryData\BusinessDays $businessDays;

    protected \MageSuite\GoogleStructuredData\Provider\Data\Product\DeliveryData\CutoffTime $cutoffTime;

    protected \MageSuite\GoogleStructuredData\Provider\Data\Product\DeliveryData\HandlingTime $handlingTime;

    protected \MageSuite\GoogleStructuredData\Provider\Data\Product\DeliveryData\TransitTime $transitTime;

    protected \Magento\Framework\DataObjectFactory $dataObjectFactory;

    protected \Magento\Framework\Stdlib\ArrayManager $arrayManager;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Escaper $escaper,
        \MageSuite\GoogleStructuredData\Provider\Data\Product\CompositeAttribute $compositeAttributeDataProvider,
        \MageSuite\GoogleStructuredData\Model\Review\GetProductReviews $getProductReviews,
        \MageSuite\GoogleStructuredData\Model\Review\GetProductRattingSummary $getProductRattingSummary,
        \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration,
        \MageSuite\GoogleStructuredData\Helper\Configuration $configuration,
        \MageSuite\GoogleStructuredData\Provider\Data\Product\DeliveryData\BusinessDays $businessDays,
        \MageSuite\GoogleStructuredData\Provider\Data\Product\DeliveryData\CutoffTime $cutoffTime,
        \MageSuite\GoogleStructuredData\Provider\Data\Product\DeliveryData\HandlingTime $handlingTime,
        \MageSuite\GoogleStructuredData\Provider\Data\Product\DeliveryData\TransitTime $transitTime,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\Stdlib\ArrayManager $arrayManager
    ) {
        $this->timezone = $timezone;
        $this->escaper = $escaper;
        $this->compositeAttributeDataProvider = $compositeAttributeDataProvider;
        $this->getProductReviews = $getProductReviews;
        $this->getProductRattingSummary = $getProductRattingSummary;
        $this->productConfiguration = $productConfiguration;
        $this->configuration = $configuration;
        $this->businessDays = $businessDays;
        $this->cutoffTime = $cutoffTime;
        $this->handlingTime = $handlingTime;
        $this->transitTime = $transitTime;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->arrayManager = $arrayManager;
    }

    public function isApplicable($productTypeId): bool
    {
        return true;
    }

    public function execute(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store, bool $withReviews = true): array
    {
        return $this->getProductStructuredData($product, $store, $withReviews);
    }

    public function getProductStructuredData(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store, bool $withReviews = true): array
    {
        $productData = $this->getBaseProductData($product, $store);
        $offerData = $this->getOffers($product, $store);

        $reviewsData = $withReviews ? $this->getReviewsData($product, $store) : [];

        return array_merge($productData, $offerData, $reviewsData);
    }

    public function getBaseProductData(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store): array
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

    public function getOffers(\Magento\Catalog\Api\Data\ProductInterface $product, $store): array
    {
        $currency = $store->getCurrentCurrencyCode();

        return [
            'offers' => $this->getOfferData($product, $store, $currency)
        ];
    }

    public function getOfferData(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store, $currency): array
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

        $dataObject = $this->dataObjectFactory->create();
        $dataObject->setData('store', $store);
        $dataObject->setData('product', $product);
        $dataObject->setData('currency', $currency);
        $deliveryData = $this->getDeliveryData($dataObject);

        if ($deliveryData) {
            $data['shippingDetails'] = $deliveryData;
        }

        return $data;
    }

    public function getReviewsData(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store): array
    {
        if (!$this->productConfiguration->shouldShowRating()) {
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
                'author' => ['@type' => 'Person', 'name' => $this->escaper->escapeHtml($review->getNickname())],
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

    public function getDeliveryData(\Magento\Framework\DataObject $dataObject): array
    {
        $store = $dataObject->getStore();
        $storeId = $store->getId();

        if (!$this->productConfiguration->isDeliveryDataEnabled($storeId)) {
            return [];
        }

        $availableCarriers = $this->getAvailableCarriers($store);

        if (!$availableCarriers) {
            return [];
        }

        $businessDaysData = $this->businessDays->getBusinessDaysData($dataObject);
        $handlingTimeData = $this->handlingTime->getHandlingTimeData($dataObject);
        $transitTimeData = $this->transitTime->getTransitTimeData($dataObject);
        $cutoffTimeData = $this->cutoffTime->getCutoffTimeData($dataObject);

        $deliveryTimeData = ["@type" => "ShippingDeliveryTime"];
        $deliveryTimeData = array_merge($deliveryTimeData, $businessDaysData);
        $deliveryTimeData = array_merge($deliveryTimeData, $cutoffTimeData);
        $deliveryTimeData = array_merge($deliveryTimeData, $handlingTimeData);
        $deliveryTimeData = array_merge($deliveryTimeData, $transitTimeData);

        $shippingDestination = [
            "@type" => "DefinedRegion",
            "addressCountry" => $this->configuration->getCountryByWebsite($store->getWebsite())
        ];

        $data = [];
        foreach ($availableCarriers as $carrier) {
            $shippingRateData = [
                "@type" => "MonetaryAmount",
                "value" => $carrier['price'],
                "currency" => $dataObject->getData('currency')
            ];

            $offerShippingDetails = [
                '@type' => 'OfferShippingDetails',
                'deliveryTime' => $deliveryTimeData,
                'shippingRate' => $shippingRateData,
                'shippingDestination' => $shippingDestination
            ];

            $data[] = $offerShippingDetails;
        }

        return $data;
    }

    public function getAvailableCarriers(\Magento\Store\Api\Data\StoreInterface $store): array
    {
        $allCarriers =  $this->configuration->getCarriers($store);

        $availableCarriers = [];
        foreach ($allCarriers as $carrierCode => $carrier) {
            $isActive = $this->arrayManager->get('active', $carrier);
            if (!(bool) $isActive) {
                continue;
            }

            if (!$this->isShippingMethodAvailable($carrier, $store)) {
                continue;
            }

            $price = $this->arrayManager->get('price', $carrier);
            if ($price === null) {
                continue;
            }

            $availableCarriers[$carrierCode] = [
                'name' => $carrier['name'],
                'price' => $price
            ];
        }

        return $availableCarriers;
    }

    public function isShippingMethodAvailable(array $carrier, \Magento\Store\Api\Data\StoreInterface $store): bool
    {
        $allowSpecificCountries = $this->arrayManager->get('sallowspecific', $carrier);
        if ((bool) $allowSpecificCountries) {
            if ($this->isShippingMethodAvailableForWebsite($carrier, $store)) {
                return true;
            }

            return false;
        }

        return true;
    }

    public function isShippingMethodAvailableForWebsite(array $carrier, \Magento\Store\Api\Data\StoreInterface $store): bool
    {
        $websiteCountry = $this->configuration->getCountryByWebsite($store->getWebsite());
        if (!$websiteCountry) {
            return false;
        }

        $specificCountries = $this->getSpecificCountries($carrier);
        foreach ($specificCountries as $countryCode) {
            if ($countryCode == $websiteCountry) {
                return true;
            }
        }

        return false;
    }

    public function getSpecificCountries(array $carrier): array
    {
        $allowedCountries = $this->arrayManager->get('specificcountry', $carrier);

        $specificCountries = [];
        if ($allowedCountries) {
            $specificCountries = explode(',', $allowedCountries);
        }

        return $specificCountries;
    }
}
