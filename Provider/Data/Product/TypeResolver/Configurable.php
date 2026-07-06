<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolver;

class Configurable extends DefaultResolver implements \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverInterface
{
    protected array $productSuperAttributes = [];

    public function execute(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store): array
    {
        $productData = $this->getProductStructuredData($product, $store);
        $productGroupData = $this->getProductGroupData($product, $store);
        $reviewsData = $this->getReviewsData($product, $store);

        $groupDataAndReviews = array_merge($productGroupData, $reviewsData);
        if (empty($groupDataAndReviews)) {
            return [$productData];
        }

        return [$groupDataAndReviews, $productData];
    }

    public function isApplicable(string $productTypeId): bool
    {
        return $productTypeId === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE;
    }

    public function getOffers(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store): array
    {
        $data = [
            'offers' => []
        ];
        $currency = $store->getCurrentCurrencyCode();

        $simpleProducts = $product->getTypeInstance()->getUsedProducts($product);
        $this->inventoryData->addStockDataToProducts($simpleProducts, (int)$store->getId());
        $this->batchProductUrlData->preloadForProducts($simpleProducts, (int)$store->getId());
        $productUrl = $product->getProductUrl();

        foreach ($simpleProducts as $simpleProduct) {
            $offer = $this->getOfferData($simpleProduct, $store, $currency);
            $offer['url'] = $productUrl;

            $data['offers'][] = $offer;
        }

        return $data;
    }

    protected function getProductGroupData(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store): array
    {
        if (!$this->productConfiguration->isProductGroupElementDisplayedForConfigurable()) {
            return [];
        }

        $variants = $this->getVariants($product, $store);
        $groupData = [
            '@context' => 'https://schema.org/',
            '@type' => 'ProductGroup',
            'name' => $this->escaper->escapeHtml($product->getName()),
            'description' => $this->getDescription($product, (int)$store->getId()),
            'productGroupID' => $product->getSku(),
            'url' => $product->getProductUrl(),
            'variesBy' => $this->getVariesBy($product)
        ];

        if (!empty($variants)) {
            $groupData['hasVariant'] = $variants;
        }

        return $groupData;
    }

    protected function getVariesBy(\Magento\Catalog\Api\Data\ProductInterface $product): array
    {
        if ($product->getTypeId() !== \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            return [];
        }

        $result = [];
        $superAttributes = $this->getProductSuperAttributes($product);
        foreach ($superAttributes as $attribute) {
            if (!$attribute->getVariesBy()) {
                continue;
            }

            $result[] = $attribute->getVariesBy();
        }

        return $result;
    }

    public function getVariants(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store): array
    {
        $superAttributes = $this->getProductSuperAttributes($product);
        $simpleProducts = $product->getTypeInstance()->getUsedProducts($product);
        $this->batchProductUrlData->preloadForProducts($simpleProducts, (int)$store->getId());
        $productUrl = $product->getProductUrl();

        $isUseParentProductUrl = $this->productConfiguration->isUseParentProductUrlForConfigurable();
        $isUseParentImages = $this->productConfiguration->isUseParentProductImagesForConfigurable();
        $isUseParentName = $this->productConfiguration->isUseParentProductNameForConfigurable();
        $isUseParentDescription = $this->productConfiguration->isUseParentProductDescriptionForConfigurable();

        $result = [];
        foreach ($simpleProducts as $simpleProduct) {
            $variant = $this->getBaseProductData($simpleProduct, $store);
            $variant['offers'] = $this->getOfferData($simpleProduct, $store, $store->getCurrentCurrencyCode());
            $variant['url'] = $productUrl;

            if ($isUseParentProductUrl) {
                $variant['offers']['url'] = $productUrl;
            }

            if (!$isUseParentProductUrl) {
                $variant['offers']['url'] = $variant['offers']['url'] ?? sprintf('%s%s', $store->getBaseUrl(), $simpleProduct->getUrlKey());
            }

            if ($isUseParentImages || empty($variant['image'])) {
                $variant['image'] = $this->getProductImages($product, $store);
            }

            if ($isUseParentName || empty($variant['name'])) {
                $variant['name'] = $product->getName();
            }

            if ($isUseParentDescription || empty($variant['description'])) {
                $variant['description'] = $this->getDescription($product, (int)$store->getId());
            }

            foreach ($superAttributes as $attribute) {
                $varyAttributeCode = $attribute->getVaryAttributeCode() ?? $attribute->getAttributeCode();
                $variant[$varyAttributeCode] = $this->batchAttributeOptionData->getOptionText(
                    $attribute->getAttributeCode(),
                    (int)$store->getId(),
                    $simpleProduct->getData($attribute->getAttributeCode())
                );
            }

            $result[] = $variant;
        }

        return $result;
    }

    protected function getProductSuperAttributes(\Magento\Catalog\Api\Data\ProductInterface $product): array
    {
        if (isset($this->productSuperAttributes[$product->getId()])) {
            return $this->productSuperAttributes[$product->getId()];
        }

        $productTypeInstance = $product->getTypeInstance();
        $productTypeInstance->setStoreFilter($product->getStoreId(), $product);
        $superAttributes = $productTypeInstance->getConfigurableAttributes($product);

        foreach ($superAttributes as $attribute) {
            $this->productSuperAttributes[$product->getId()][$attribute->getAttributeId()] = $attribute->getProductAttribute();
        }

        return $this->productSuperAttributes[$product->getId()] ?? [];
    }
}
