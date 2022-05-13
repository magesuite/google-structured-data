<?php

namespace MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolver;

class Configurable extends DefaultResolver implements \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverInterface
{
    public function isApplicable($productTypeId)
    {
        return $productTypeId == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE;
    }

    public function getOffers(\Magento\Catalog\Api\Data\ProductInterface $product, $store)
    {
        $data = [];
        $currency = $store->getCurrentCurrencyCode();

        $simpleProducts = $product->getTypeInstance()->getUsedProducts($product);
        $productUrl = $product->getProductUrl();

        foreach ($simpleProducts as $simpleProduct) {
            $offer = $this->getOfferData($simpleProduct, $store, $currency);
            $offer['url'] = $productUrl;

            $data['offers'][] = $offer;
        }

        return $data;
    }
}