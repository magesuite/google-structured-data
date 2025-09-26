<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Observer;

class AddStructuredDataToProductView implements \Magento\Framework\Event\ObserverInterface
{
    public function __construct(
        protected \Magento\Framework\Registry $registry,
        protected \Magento\Framework\DataObjectFactory $dataObjectFactory,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $configuration,
        protected \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        protected \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider
    ) {}

    public function execute(\Magento\Framework\Event\Observer $observer): void
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

    public function getProduct(): ?\Magento\Catalog\Model\Product
    {
        return $this->registry->registry('current_product');
    }
}
