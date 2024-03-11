<?php

namespace MageSuite\GoogleStructuredData\Plugin\Catalog\Block\Product\ListProduct;

class AddProductsDataToCategoryPage
{
    protected \Magento\Framework\Registry $registry;
    protected \Magento\Framework\DataObjectFactory $dataObjectFactory;
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;
    protected \MageSuite\GoogleStructuredData\Model\ProductStructuredDataIndexRepository $productStructuredDataIndexRepository;
    protected \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer;
    protected \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider;
    protected \MageSuite\GoogleStructuredData\Helper\Configuration\Category $categoryConfiguration;
    protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageSuite\GoogleStructuredData\Model\ProductStructuredDataIndexRepository $productStructuredDataIndexRepository,
        \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider,
        \MageSuite\GoogleStructuredData\Helper\Configuration\Category $categoryConfiguration,
        \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration
    ) {
        $this->registry = $registry;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->storeManager = $storeManager;
        $this->productStructuredDataIndexRepository = $productStructuredDataIndexRepository;
        $this->structuredDataContainer = $structuredDataContainer;
        $this->productDataProvider = $productDataProvider;
        $this->categoryConfiguration = $categoryConfiguration;
        $this->productConfiguration = $productConfiguration;
    }

    public function afterGetLoadedProductCollection(\Magento\Catalog\Block\Product\ListProduct $subject, $result)
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
        $this->productStructuredDataIndexRepository->loadDataFromIndex($productIds, $store->getId());

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
