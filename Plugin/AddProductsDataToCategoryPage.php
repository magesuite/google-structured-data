<?php
namespace MageSuite\GoogleStructuredData\Plugin;

class AddProductsDataToCategoryPage
{
    /**
    * @var \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer
    */
    protected $structuredDataContainer;
    /**
     * @var \MageSuite\GoogleStructuredData\Provider\Data\Product
     */
    protected $productDataProvider;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    public function __construct(
        \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider,
        \Magento\Framework\Event\ManagerInterface $eventManager
    )
    {
        $this->structuredDataContainer = $structuredDataContainer;
        $this->productDataProvider = $productDataProvider;
        $this->eventManager = $eventManager;
    }
    public function afterGetLoadedProductCollection(\Magento\Catalog\Block\Product\ListProduct $subject, $result)
    {
        $i = 0;
        foreach ($result as $product) {
            $productData = $this->productDataProvider->getProductStructuredData($product);

            unset($productData['review']);

            $productDataObject = new \Magento\Framework\DataObject();

            $productDataObject->setData($productData);

            $this->eventManager->dispatch('add_product_structured_data_after', ['structured_data' => $productDataObject, 'node' => 'product_' . $i, 'product' => $product]);

            $this->structuredDataContainer->add($productDataObject->getData(), 'product_' . $i);

            $i++;
        }

        return $result;
    }
}
