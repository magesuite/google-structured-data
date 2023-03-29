<?php

namespace MageSuite\GoogleStructuredData\Plugin\Catalog\Block\Product\ListProduct;

class AddProductsDataToCategoryPage
{
    protected \Magento\Framework\DataObjectFactory $dataObjectFactory;
    protected \MageSuite\GoogleStructuredData\Helper\Configuration $configuration;
    protected \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer;
    protected \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider;
    protected \Magento\Framework\Registry $registry;

    public function __construct(
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \MageSuite\GoogleStructuredData\Helper\Configuration $configuration,
        \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider,
        \Magento\Framework\Registry $registry
    ) {
        $this->configuration = $configuration;
        $this->structuredDataContainer = $structuredDataContainer;
        $this->productDataProvider = $productDataProvider;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->registry = $registry;
    }

    public function afterGetLoadedProductCollection(\Magento\Catalog\Block\Product\ListProduct $subject, $result)
    {
        if (!$this->configuration->isCategoryPageIncludeProducts()) {
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

        $result->addMediaGalleryData();
        foreach ($result as $product) {
            $productData = $this->productDataProvider->execute($product, false);

            $productDataObject = $this->dataObjectFactory->create();
            $productDataObject->setData($productData);

            $this->structuredDataContainer->add($productDataObject->getData(), 'product_' . $i);

            $i++;
        }

        $subject->setStructuredDataCalculated(true);

        return $result;
    }
}
