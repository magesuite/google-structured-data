<?php
namespace MageSuite\GoogleStructuredData\Observer;

class GenerateProductStructuredData implements \Magento\Framework\Event\ObserverInterface
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
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Registry $registry
    )
    {
        $this->structuredDataContainer = $structuredDataContainer;
        $this->productDataProvider = $productDataProvider;
        $this->scopeConfig = $scopeConfig;
        $this->eventManager = $eventManager;
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if(!$this->scopeConfig->getValue('structured_data/product_page/enabled')){
            return;
        }
        $productData = $this->productDataProvider->getProductStructuredData();

        $productDataObject = new \Magento\Framework\DataObject();

        $productDataObject->setData($productData);

        $this->eventManager->dispatch('add_product_structured_data_after', ['structured_data' => $productDataObject, 'node' => 'product', 'product' => $this->getProduct()]);

        $this->structuredDataContainer->add($productDataObject->getData(), 'product');
    }

    public function getProduct()
    {
        $product = $this->registry->registry('current_product');

        if(!$product){
            return false;
        }

        return $product;
    }
}