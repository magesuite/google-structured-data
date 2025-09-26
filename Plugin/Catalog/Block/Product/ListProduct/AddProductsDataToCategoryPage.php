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
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration
    ) {}

    public function afterGetLoadedProductCollection(\Magento\Catalog\Block\Product\ListProduct $subject, $result) // phpcs:ignore
    {
        if (!$this->categoryConfiguration->doesCategoryPageIncludeProducts()) {
            return $result;
        }

        /** @var \Magento\Catalog\Model\Category|null $currentCategory */
        $currentCategory = $this->registry->registry('current_category');

        if (!isset($currentCategory) || !$currentCategory->getId()) {
            return $result;
        }

        if ($subject->getStructuredDataCalculated() === true) {
            return $result;
        }

        $i = 0;
        $shouldShowRating = $this->categoryConfiguration->shouldShowRating();

        $productIds = $result->getColumnValues('entity_id');
        if (empty($productIds)) {
            return $result;
        }

        $store = $this->storeManager->getStore();
        $this->productStructuredDataIndexRepository->loadDataFromIndex($productIds, (int)$store->getId());

        if (!$this->productConfiguration->isIndexingEnabled()) {
            $result->addMediaGalleryData();
        }

        foreach ($result as $product) {
            $productData = $this->productDataProvider->getProductData($product, $store);
            if (!$shouldShowRating) {
                unset($productData['review']);
            }

            $productDataObject = $this->dataObjectFactory->create();
            $productDataObject->setData($productData);

            $this->structuredDataContainer->add($productDataObject->getData(), 'product_' . $i);

            $i++;
        }

        $subject->setStructuredDataCalculated(true);

        return $result;
    }
}
