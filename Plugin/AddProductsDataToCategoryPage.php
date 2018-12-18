<?php
namespace MageSuite\GoogleStructuredData\Plugin;

class AddProductsDataToCategoryPage
{
    /**
    * @var \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer
    */
    private $structuredDataContainer;
    /**
     * @var \MageSuite\GoogleStructuredData\Provider\Data\Product
     */
    private $productDataProvider;

    public function __construct(
        \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider
    )
    {
        $this->structuredDataContainer = $structuredDataContainer;
        $this->productDataProvider = $productDataProvider;
    }
    public function afterGetLoadedProductCollection(\Magento\Catalog\Block\Product\ListProduct $subject, $result)
    {
        $categoryProducts = $result;


        $i = 0;
        foreach ($categoryProducts as $product) {
            $productData = $this->productDataProvider->getProductStructuredData($product);

            $structuredDataContainer = $this->structuredDataContainer;

            $structuredDataContainer->add($productData, $structuredDataContainer::PRODUCT . '_' . $i);

            $i++;
        }

        return $result;
    }
}
