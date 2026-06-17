<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Plugin\Catalog\Block\Product\ListProduct;

class AddProductsDataToCategoryPage
{
    public function __construct(
        protected \Magento\Framework\Registry $registry,
        protected \Magento\Framework\DataObjectFactory $dataObjectFactory,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
        protected \MageSuite\GoogleStructuredData\Model\ProductStructuredDataIndexRepository $productStructuredDataIndexRepository,
        protected \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        protected \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider,
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Category $categoryConfiguration,
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration,
        protected \MageSuite\GoogleStructuredData\Model\Product\AttributeList $attributeList
    ) {
    }

    public function afterGetLoadedProductCollection(\Magento\Catalog\Block\Product\ListProduct $subject, $result) // phpcs:ignore
    {
        if ($this->shouldSkip($subject)) {
            return $result;
        }

        $store = $this->storeManager->getStore();
        $storeId = (int)$store->getId();
        $isIndexingEnabled = $this->productConfiguration->isIndexingEnabled();

        if (!$isIndexingEnabled) {
            $result->addAttributeToSelect($this->attributeList->getList($storeId));
            $result->_loadAttributes(); // phpcs:ignore
        }

        $productIds = $result->getColumnValues('entity_id');
        if (empty($productIds)) {
            return $result;
        }

        if (!$isIndexingEnabled) {
            $result->addMediaGalleryData();
        }

        $this->productStructuredDataIndexRepository->loadDataFromIndex($productIds, $storeId);

        foreach ($result as $product) {
            $this->addProductStructuredData($product, $store);
        }

        $subject->setStructuredDataCalculated(true);

        return $result;
    }

    protected function addProductStructuredData(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        \Magento\Store\Api\Data\StoreInterface $store
    ): void {
        $shouldShowRating = $this->categoryConfiguration->shouldShowRating();
        $productData = $this->productDataProvider->getProductData($product, $store);

        if (!$shouldShowRating) {
            $productData = $this->removeReviewAndAggregateRating($product->getTypeId(), $productData);
        }

        $productDataObject = $this->dataObjectFactory->create();
        $productDataObject->setData($productData);
        $this->structuredDataContainer->add($productDataObject->getData(), 'product_' . $product->getId());
    }

    protected function shouldSkip(\Magento\Catalog\Block\Product\ListProduct $subject): bool
    {
        if (!$this->categoryConfiguration->doesCategoryPageIncludeProducts()) {
            return true;
        }

        $currentCategory = $this->registry->registry('current_category');

        if (!isset($currentCategory) || !$currentCategory->getId()) {
            return true;
        }

        return $subject->getStructuredDataCalculated() === true;
    }

    protected function removeReviewAndAggregateRating(string $productTypeId, array $productData): array
    {
        if ($productTypeId != \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
            unset($productData['review'], $productData['aggregateRating']);
            return $productData;
        }

        foreach ($productData as &$childProductData) {
            unset($childProductData['review'], $childProductData['aggregateRating']);
        }

        return $productData;
    }
}
