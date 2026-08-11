<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolver;

class Grouped extends DefaultResolver implements \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverInterface
{
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

        foreach ($associatedProducts as $associatedProduct) {
            $associatedProductData = $this->getProductStructuredData($associatedProduct, $store);

            if (empty($associatedProductData)) {
                continue;
            }

            if ($this->productConfiguration->isUseParentProductUrlForGrouped()) {
                $associatedProductData['url'] = $this->getParentProduct()->getProductUrl();
            }
            if ($this->productConfiguration->isUseParentProductImagesForGrouped()) {
                $associatedProductData['image'] = $this->getProductImages($this->getParentProduct(), $store);
            }

            $productData[] = $this->applyVariantNodeId($associatedProductData, $associatedProduct, $store);
        }

        return $productData;
    }

    protected function applyVariantNodeId(
        array $associatedProductData,
        \Magento\Catalog\Api\Data\ProductInterface $associatedProduct,
        \Magento\Store\Api\Data\StoreInterface $store
    ): array {
        $parentNodeId = $this->getProductNodeId($this->getParentProduct(), $store);

        if ($parentNodeId === null) {
            unset($associatedProductData['@id']);

            return $associatedProductData;
        }

        $associatedProductData['@id'] = sprintf('%s-%d', $parentNodeId, $associatedProduct->getId());

        return $associatedProductData;
    }

    public function getOfferData(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store, string $currency): array
    {
        $offerData = parent::getOfferData($product, $store, $currency);

        if ($this->productConfiguration->isUseParentProductUrlForGrouped()) {
            $offerData['url'] = $this->getParentProduct()->getProductUrl();
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
