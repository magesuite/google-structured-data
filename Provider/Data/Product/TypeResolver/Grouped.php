<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolver;

class Grouped extends DefaultResolver implements \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverInterface
{
    protected const MAX_PRODUCT_GROUP_REVIEWS = 10;

    protected ?\Magento\Catalog\Api\Data\ProductInterface $parentProduct = null;

    public function isApplicable(string $productTypeId): bool
    {
        return $productTypeId === \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE;
    }

    public function execute(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store): array
    {
        $productData = [];
        $this->setParentProduct($product);
        $associatedProducts = $product->getTypeInstance()->getAssociatedProducts($product);
        $this->inventoryData->addStockDataToProducts($associatedProducts, (int)$store->getId());
        $this->batchProductUrlData->preloadForProducts($associatedProducts, (int)$store->getId());
        $this->batchProductUrlData->preloadCanonical($this->getProductIds($associatedProducts), (int)$store->getId());
        $this->batchRuleEndDateData->load($this->getProductIds($associatedProducts), (int)$store->getId());
        $this->batchReviewData->load($this->getReviewProductIds($product, $associatedProducts, $store), (int)$store->getId());

        foreach ($associatedProducts as $associatedProduct) {
            $associatedProductData = $this->getProductStructuredData($associatedProduct, $store);

            if (empty($associatedProductData)) {
                continue;
            }

            if ($this->productConfiguration->isUseParentProductUrlForGrouped()) {
                $associatedProductData['url'] = $this->batchProductUrlData->getProductUrl($this->getParentProduct(), $store);
            }
            if ($this->productConfiguration->isUseParentProductImagesForGrouped()) {
                $associatedProductData['image'] = $this->getProductImages($this->getParentProduct(), $store);
            }

            $productData[] = $this->applyVariantNodeId($associatedProductData, $associatedProduct, $store);
        }

        return $this->addProductGroupData($productData, $associatedProducts, $store);
    }

    protected function getReviewProductIds(\Magento\Catalog\Api\Data\ProductInterface $product, array $associatedProducts, \Magento\Store\Api\Data\StoreInterface $store): array
    {
        if ($this->productConfiguration->isUseParentProductReviewsForGrouped() && !$this->productConfiguration->isProductGroupElementDisplayedForGrouped((int)$store->getId())) {
            return [(int)$product->getId()];
        }

        return array_merge([(int)$product->getId()], $this->getProductIds($associatedProducts));
    }

    protected function applyVariantNodeId(array $associatedProductData, \Magento\Catalog\Api\Data\ProductInterface $associatedProduct, \Magento\Store\Api\Data\StoreInterface $store): array
    {
        $parentNodeId = $this->getProductNodeId($this->getParentProduct(), $store);

        if ($parentNodeId === null) {
            unset($associatedProductData['@id']);

            return $associatedProductData;
        }

        $associatedProductData['@id'] = sprintf('%s-%d', $parentNodeId, $associatedProduct->getId());

        return $associatedProductData;
    }

    protected function addProductGroupData(array $productData, array $associatedProducts, \Magento\Store\Api\Data\StoreInterface $store): array
    {
        $productGroupId = $this->getProductNodeId($this->getParentProduct(), $store);

        if (empty($productData) || $productGroupId === null || !$this->productConfiguration->isProductGroupElementDisplayedForGrouped((int)$store->getId())) {
            return $productData;
        }

        $productGroupData = array_merge($this->getProductGroupData($productGroupId, $productData, $store), $this->getProductGroupReviewsData($associatedProducts, $store));

        foreach (array_keys($productData) as $index) {
            $productData[$index]['isVariantOf'] = ['@id' => $productGroupId];
        }

        return array_merge([$productGroupData], $productData);
    }

    protected function getProductGroupData(string $productGroupId, array $variantsData, \Magento\Store\Api\Data\StoreInterface $store): array
    {
        $parentProduct = $this->getParentProduct();

        return [
            '@type' => 'ProductGroup',
            '@id' => $productGroupId,
            'name' => $this->escaper->escapeHtml($parentProduct->getName()),
            'description' => $this->getDescription($parentProduct, (int)$store->getId()),
            'productGroupID' => $parentProduct->getSku(),
            'url' => $this->batchProductUrlData->getProductUrl($parentProduct, $store),
            'hasVariant' => array_map(static fn (array $variantData): array => ['@id' => $variantData['@id']], $variantsData)
        ];
    }

    protected function getProductGroupReviewsData(array $associatedProducts, \Magento\Store\Api\Data\StoreInterface $store): array
    {
        if (!$this->productConfiguration->shouldShowRating()) {
            return [];
        }

        $reviewsData = $this->getAggregateRatingData($this->getParentProduct(), $store);
        $reviews = $this->getProductGroupReviews($associatedProducts, (int)$store->getId());

        if (empty($reviews)) {
            return $reviewsData;
        }

        $reviewsData['review'] = array_map(fn (\Magento\Review\Model\Review $review): array => $this->buildReviewData($review, $store), $reviews);

        return $reviewsData;
    }

    protected function getProductGroupReviews(array $associatedProducts, int $storeId): array
    {
        $productIds = array_merge([(int)$this->getParentProduct()->getId()], $this->getProductIds($associatedProducts));
        $reviews = array_merge(...array_map(fn (int $productId): array => $this->batchReviewData->getReviews($productId, $storeId), $productIds));

        usort($reviews, static fn ($left, $right): int => [$right->getCreatedAt(), (int)$right->getId()] <=> [$left->getCreatedAt(), (int)$left->getId()]);

        return array_slice($reviews, 0, self::MAX_PRODUCT_GROUP_REVIEWS);
    }

    public function getOfferData(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store, string $currency): array
    {
        $offerData = parent::getOfferData($product, $store, $currency);

        if ($this->productConfiguration->isUseParentProductUrlForGrouped()) {
            $offerData['url'] = $this->batchProductUrlData->getProductUrl($this->getParentProduct(), $store);
        }

        return $offerData;
    }

    public function getReviewsData(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store): array
    {
        $reviewProduct = $this->productConfiguration->isUseParentProductReviewsForGrouped() ? $this->getParentProduct() : $product;

        return parent::getReviewsData($reviewProduct, $store);
    }

    public function setParentProduct(\Magento\Catalog\Api\Data\ProductInterface $product): void
    {
        $this->parentProduct = $product;
    }

    public function getParentProduct(): ?\Magento\Catalog\Api\Data\ProductInterface
    {
        return $this->parentProduct;
    }
}
