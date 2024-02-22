<?php

namespace MageSuite\GoogleStructuredData\Observer;

class AddStructuredDataToProductView implements \Magento\Framework\Event\ObserverInterface
{
    protected \Magento\Framework\Registry $registry;
    protected \Magento\Framework\DataObjectFactory $dataObjectFactory;
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;
    protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $configuration;
    protected \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer;
    protected \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageSuite\GoogleStructuredData\Helper\Configuration\Product $configuration,
        \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider
    ) {
        $this->registry = $registry;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->storeManager = $storeManager;
        $this->configuration = $configuration;
        $this->structuredDataContainer = $structuredDataContainer;
        $this->productDataProvider = $productDataProvider;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $this->getProduct();

        if (!$this->configuration->isEnabled() || !$product) {
            return;
        }

        $store = $this->storeManager->getStore();

        $productData = $this->productDataProvider->getProductData($product, $store);
        $productDataObject = $this->dataObjectFactory->create();
        $productDataObject->setData($productData);

        $this->structuredDataContainer->add($productDataObject->getData(), 'product');
    }

    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }
}
