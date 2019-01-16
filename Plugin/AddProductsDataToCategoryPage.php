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
    protected $eventManager;
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->structuredDataContainer = $structuredDataContainer;
        $this->productDataProvider = $productDataProvider;
        $this->eventManager = $eventManager;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->scopeConfig = $scopeConfig;
    }
    public function afterGetLoadedProductCollection(\Magento\Catalog\Block\Product\ListProduct $subject, $result)
    {
        if(!$this->scopeConfig->getValue('structured_data/category_page/include_products')){
            return $result;
        }

        $i = 0;
        foreach ($result as $product) {
            $productData = $this->productDataProvider->getProductStructuredData($product);

            unset($productData['review']);

            $productDataObject = $this->dataObjectFactory->create();

            $productDataObject->setData($productData);

            $this->eventManager->dispatch('add_product_structured_data_after', ['structured_data' => $productDataObject, 'node' => 'product_' . $i, 'product' => $product]);

            $this->structuredDataContainer->add($productDataObject->getData(), 'product_' . $i);

            $i++;
        }

        return $result;
    }
}
