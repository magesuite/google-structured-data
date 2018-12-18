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
        $i = 0;
        foreach ($result as $product) {
            $productData = $this->productDataProvider->getProductStructuredData($product);
            unset($productData['review']);

            $this->structuredDataContainer->add($productData, 'product_' . $i);

            $i++;
        }

        return $result;
    }
}
