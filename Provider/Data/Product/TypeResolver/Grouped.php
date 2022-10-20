<?php

namespace MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolver;

class Grouped extends DefaultResolver implements \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverInterface
{
    protected \Magento\Catalog\Api\Data\ProductInterface $parentProduct;

    public function isApplicable($productTypeId): bool
    {
        return $productTypeId == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE;
    }

    public function execute(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store, bool $withReviews = true): array
    {
        $productData = [];

        $this->setParentProduct($product);
        $associatedProducts = $product->getTypeInstance()->getAssociatedProducts($product);
        foreach ($associatedProducts as $associatedProduct) {
            $associatedProductData = $this->getProductStructuredData($associatedProduct, $store, $withReviews);

            if ($this->configuration->isUseParentProductUrlForGrouped()) {
                $associatedProductData['url'] = $this->getParentProduct()->getProductUrl();
            }
            if ($this->configuration->isUseParentProductImagesForGrouped()) {
                $associatedProductData['image'] = $this->getProductImages($this->getParentProduct());
            }

            $productData[] = $associatedProductData;
        }

        return $productData;
    }

    public function getOfferData(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store, $currency): array
    {
        $offerData = parent::getOfferData($product, $store, $currency);

        if ($this->configuration->isUseParentProductUrlForGrouped()) {
            $offerData['url'] = $this->getParentProduct()->getProductUrl();
        }

        return $offerData;
    }

    public function getReviewsData(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store): array
    {
        $reviewProduct = $this->configuration->isUseParentProductReviewsForGrouped() ? $this->getParentProduct() : $product;

        return parent::getReviewsData($reviewProduct, $store);
    }

    public function setParentProduct(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $this->parentProduct = $product;
    }

    public function getParentProduct()
    {
        return $this->parentProduct;
    }
}
