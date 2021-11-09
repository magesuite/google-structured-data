<?php

namespace MageSuite\GoogleStructuredData\Provider\Data;

class Product
{
    const IN_STOCK = 'InStock';
    const OUT_OF_STOCK = 'OutOfStock';

    const CACHE_KEY = 'google_structured_data_product_%s_%s';
    const CACHE_GROUP = 'google_structured_data_product';

    protected $attributesCache = [];

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

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
     * @var \Magento\Eav\Model\Entity\Attribute
     */
    protected $attribute;

    /**
     * @var \MageSuite\GoogleStructuredData\Helper\Configuration\Product
     */
    protected $configuration;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \MageSuite\GoogleStructuredData\Repository\ProductReviews $productReviews,
        \Magento\Eav\Model\Entity\Attribute $attribute,
        \MageSuite\GoogleStructuredData\Helper\Configuration\Product $configuration,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->localeDate = $localeDate;
        $this->storeManager = $storeManager;
        $this->reviewFactory = $reviewFactory;
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->productRepository = $productRepository;
        $this->productReviews = $productReviews;
        $this->attribute = $attribute;
        $this->configuration = $configuration;
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->escaper = $escaper;
    }

    public function getProductStructuredData(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $cacheKey = $this->getCacheKey($product);

        if (($cachedData = $this->cache->load($cacheKey)) != false) {
            return $this->serializer->unserialize($cachedData);
        }

        $productData = $this->getBaseProductData($product);
        $offerData = $this->getOffers($product);
        $reviewsData = $this->getReviewsData($product);

        $result = array_merge($productData, $offerData, $reviewsData);

        $identities = $this->getIdentities($product);

        $this->cache->save(
            $this->serializer->serialize($result),
            $cacheKey,
            $identities
        );

        return $result;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return array
     */
    protected function getBaseProductData(\Magento\Catalog\Api\Data\ProductInterface $product)
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

        $attributes = ['description', 'brand', 'manufacturer'];
        foreach ($attributes as $attribute) {
            $methodName = 'get' . ucfirst($attribute);
            if (!method_exists($this->configuration, $methodName)) {
                continue;
            }

            $attributeCode = $this->configuration->$methodName();
            if (empty($attributeCode)) {
                continue;
            }

            try {
                $structuredData[$attribute] = $this->getAttributeValue($product, $attributeCode);
            } catch (\Exception $e) {
                // do nothing
            }
        }

        return $structuredData;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return array
     */
    protected function getProductImages(\Magento\Catalog\Api\Data\ProductInterface $product)
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

    protected function getOffers(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $currency = $this->storeManager->getStore()->getCurrentCurrencyCode();
        $data = [];

        if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $simpleProducts = $product->getTypeInstance()->getUsedProducts($product);

            foreach ($simpleProducts as $simpleProduct) {
                $data['offers'][] = $this->getOfferData($simpleProduct, $currency, $product);
            }
        } else {
            $data['offers'] = $this->getOfferData($product, $currency);
        }

        return $data;
    }

    protected function getOfferData(\Magento\Catalog\Api\Data\ProductInterface $product, $currency, $configurableProduct = null)
    {
        $data = [
            '@type' => 'Offer',
            'sku' => $this->escaper->escapeHtml($product->getSku()),
            'price' => number_format($this->getProductPrice($product), 2, '.', ''),
            'priceCurrency' => $currency,
            'availability' => $product->getIsSalable() ? self::IN_STOCK : self::OUT_OF_STOCK,
            'url' => $configurableProduct ? $configurableProduct->getProductUrl() : $product->getProductUrl()
        ];

        $store = $product->getStore();
        $specialFromDate = $product->getSpecialFromDate();
        $specialToDate = $product->getSpecialToDate();
        $inRange = $this->localeDate->isScopeDateInInterval($store, $specialFromDate, $specialToDate);

        if ($product->getSpecialPrice() && $specialToDate && $inRange) {
            $data['priceValidUntil'] = date('Y-m-d', strtotime($specialToDate));
        }

        return $data;
    }

    protected function getProductPrice(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        return $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
    }

    protected function getReviewsData(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        if (!$this->configuration->isShowRating()) {
            return [];
        }

        $data = [];
        $ratingSummary = $this->productReviews->getRatingSummary($product);

        if ($ratingSummary['rating_value'] && $ratingSummary['review_count']) {
            $data['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $ratingSummary['rating_value'],
                'reviewCount' => $ratingSummary['review_count']
            ];
        }

        $reviews = $this->productReviews->getReviews($product)
            ->setDateOrder()
            ->addStoreFilter($this->storeManager->getStore()->getId());
        $reviews->getSelect()
            ->joinLeft(
                ['rov' => $reviews->getTable('rating_option_vote')],
                'main_table.review_id = rov.review_id',
                ['percent']
            )->group('main_table.review_id');
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

    protected function getAttributeValue(\Magento\Catalog\Api\Data\ProductInterface $product, $attributeCode)
    {
        $attribute = $this->getAttribute($attributeCode);
        $attributeType = $attribute->getFrontendInput();
        $types = ['select', 'multiselect'];

        if (in_array($attributeType, $types)) {
            $value = $product->getAttributeText($attributeCode);
        } else {
            $value = $product->getData($attributeCode);
        }

        return $this->escaper->escapeHtml($value);
    }

    protected function getAttribute($attributeCode)
    {
        if (!isset($this->attributesCache[$attributeCode])) {
            $attribute = $this->attribute->loadByCode(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
            $this->attributesCache[$attributeCode] = clone $attribute;
        }

        return $this->attributesCache[$attributeCode];
    }

    protected function getCacheKey(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        return sprintf(
            self::CACHE_KEY,
            $product->getId(),
            $this->storeManager->getStore()->getId()
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
