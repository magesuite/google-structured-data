<?php

namespace MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolver;

class Grouped extends DefaultResolver implements \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverInterface
{
    /**
     * @var string
     */
    protected $parentProductUrl;

    public function isApplicable($productTypeId)
    {
        return $productTypeId == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE;
    }

    public function execute(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store, bool $withReviews = true)
    {
        $productData = [];

        $this->setParentProductUrl($product);
        $associatedProducts = $product->getTypeInstance()->getAssociatedProducts($product);
        foreach ($associatedProducts as $associatedProduct) {
            $associatedProductData = $this->getProductStructuredData($associatedProduct, $store, $withReviews);
            $associatedProductData['url'] = $this->getParentProductUrl();
            $productData[] = $associatedProductData;
        }

        return $productData;
    }

    public function getOfferData(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store, $currency)
    {
        $offerData = parent::getOfferData($product, $store, $currency);

        $offerData['url'] = $this->getParentProductUrl();

        return $offerData;
    }

    public function setParentProductUrl(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $this->parentProductUrl = $product->getProductUrl();
    }

    public function getParentProductUrl()
    {
        return $this->parentProductUrl;
    }
}
