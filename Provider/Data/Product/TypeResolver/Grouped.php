<?php

namespace MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolver;

class Grouped extends DefaultResolver implements \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverInterface
{
    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface
     */
    protected $parentProduct;

    /**
     * @var \MageSuite\GoogleStructuredData\Model\Product\Grouped\GetAssociatedProducts
     */
    protected $getAssociatedProducts;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Escaper $escaper,
        \MageSuite\GoogleStructuredData\Provider\Data\Product\CompositeAttribute $compositeAttributeDataProvider,
        \MageSuite\GoogleStructuredData\Model\Product\Grouped\GetAssociatedProducts $getAssociatedProducts,
        \MageSuite\GoogleStructuredData\Model\Review\GetProductReviews $getProductReviews,
        \MageSuite\GoogleStructuredData\Model\Review\GetProductRattingSummary $getProductRattingSummary,
        \MageSuite\GoogleStructuredData\Helper\Configuration\Product $configuration
    ) {
        parent::__construct($timezone, $escaper, $compositeAttributeDataProvider, $getProductReviews, $getProductRattingSummary, $configuration);

        $this->getAssociatedProducts = $getAssociatedProducts;
    }

    public function isApplicable($productTypeId)
    {
        return $productTypeId == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE;
    }

    public function execute(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store, bool $withReviews = true)
    {
        $productData = [];

        $this->setParentProduct($product);
        $associatedProducts = $this->getAssociatedProducts->execute($product, $this->compositeAttributeDataProvider->getEavAttributeCodes());
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

    public function getOfferData(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store, $currency)
    {
        $offerData = parent::getOfferData($product, $store, $currency);

        if ($this->configuration->isUseParentProductUrlForGrouped()) {
            $offerData['url'] = $this->getParentProduct()->getProductUrl();
        }

        return $offerData;
    }

    public function getReviewsData(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store)
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
