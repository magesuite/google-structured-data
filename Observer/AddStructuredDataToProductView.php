<?php

namespace MageSuite\GoogleStructuredData\Observer;

class AddStructuredDataToProductView implements \Magento\Framework\Event\ObserverInterface
{
    protected \Magento\Framework\DataObjectFactory $dataObjectFactory;

    protected \Magento\Framework\Registry $registry;

    protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $configuration;

    protected \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer;

    protected \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider;

    public function __construct(
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\Registry $registry,
        \MageSuite\GoogleStructuredData\Helper\Configuration\Product $configuration,
        \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->registry = $registry;
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

        $productData = $this->productDataProvider->execute($product, true);
        $productDataObject = $this->dataObjectFactory->create();
        $productDataObject->setData($productData);

        $this->structuredDataContainer->add($productDataObject->getData(), 'product');
    }

    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }
}
