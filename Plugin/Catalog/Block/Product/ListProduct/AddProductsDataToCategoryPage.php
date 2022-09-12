<?php

namespace MageSuite\GoogleStructuredData\Plugin\Catalog\Block\Product\ListProduct;

class AddProductsDataToCategoryPage
{
    protected \Magento\Framework\DataObjectFactory $dataObjectFactory;

    protected \MageSuite\GoogleStructuredData\Helper\Configuration $configuration;

    protected \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer;

    protected \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider;

    public function __construct(
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \MageSuite\GoogleStructuredData\Helper\Configuration $configuration,
        \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider
    ) {
        $this->configuration = $configuration;
        $this->structuredDataContainer = $structuredDataContainer;
        $this->productDataProvider = $productDataProvider;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    public function afterGetLoadedProductCollection(\Magento\Catalog\Block\Product\ListProduct $subject, $result)
    {
        if (!$this->configuration->isCategoryPageIncludeProducts()) {
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
