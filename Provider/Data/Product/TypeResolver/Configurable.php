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
        $this->batchProductUrlData->preloadCanonical($this->getProductIds($simpleProducts), (int)$store->getId());

        $result = [];

        foreach ($simpleProducts as $simpleProduct) {
            $result[] = $this->buildVariant([
                'product' => $product,
                'simpleProduct' => $simpleProduct,
                'store' => $store,
                'superAttributes' => $superAttributes
            ]);
        }

        return $result;
    }

    protected function buildVariant(array $context): array
    {
        $simpleProduct = $context['simpleProduct'];
        $store = $context['store'];

        $variant = $this->getBaseProductData($simpleProduct, $store);
        $variant['offers'] = $this->getOfferData($simpleProduct, $store, $store->getCurrentCurrencyCode());
        $variant['url'] = $context['product']->getProductUrl();
        $variant = $this->applyVariantOfferUrl($variant, $context);
        $variant = $this->applyParentOverrides($variant, $context);

        return $this->applyVariantAttributes($variant, $context);
    }

    protected function applyVariantOfferUrl(array $variant, array $context): array
    {
        if ($this->productConfiguration->isUseParentProductUrlForConfigurable()) {
            $variant['offers']['url'] = $context['product']->getProductUrl();

            return $variant;
        }

        $variant['offers']['url'] = $variant['offers']['url']
            ?? sprintf('%s%s', $context['store']->getBaseUrl(), $context['simpleProduct']->getUrlKey());

        return $variant;
    }

    protected function applyParentOverrides(array $variant, array $context): array
    {
        $product = $context['product'];
        $store = $context['store'];

        if ($this->productConfiguration->isUseParentProductImagesForConfigurable() || empty($variant['image'])) {
            $variant['image'] = $this->getProductImages($product, $store);
        }

        if ($this->productConfiguration->isUseParentProductNameForConfigurable() || empty($variant['name'])) {
            $variant['name'] = $product->getName();
        }

        if ($this->productConfiguration->isUseParentProductDescriptionForConfigurable() || empty($variant['description'])) {
            $variant['description'] = $this->getDescription($product, (int)$store->getId());
        }

        return $variant;
    }

    protected function applyVariantAttributes(array $variant, array $context): array
    {
        $store = $context['store'];
        $simpleProduct = $context['simpleProduct'];

        foreach ($context['superAttributes'] as $attribute) {
            $varyAttributeCode = $attribute->getVaryAttributeCode() ?? $attribute->getAttributeCode();
            $variant[$varyAttributeCode] = $this->batchAttributeOptionData->getOptionText(
                $attribute->getAttributeCode(),
                (int)$store->getId(),
                $simpleProduct->getData($attribute->getAttributeCode())
            );
        }

        return $variant;
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
