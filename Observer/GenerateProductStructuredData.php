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
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \MageSuite\GoogleStructuredData\Helper\Configuration\Product
     */
    protected $configuration;

    public function __construct(
        \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \MageSuite\GoogleStructuredData\Helper\Configuration\Product $configuration
    ) {
        $this->structuredDataContainer = $structuredDataContainer;
        $this->productDataProvider = $productDataProvider;
        $this->eventManager = $eventManager;
        $this->registry = $registry;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->configuration = $configuration;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $this->getProduct();

        if (!$this->configuration->isEnabled() || !$product) {
            return;
        }

        $productData = $this->productDataProvider->getProductStructuredData($product);
        $productDataObject = $this->dataObjectFactory->create();
        $productDataObject->setData($productData);
        $this->eventManager->dispatch(
            'add_product_structured_data_after',
            [
                'structured_data' => $productDataObject,
                'node' => 'product',
                'product' => $product
            ]
        );
        $this->structuredDataContainer->add($productDataObject->getData(), 'product');
    }

    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }
}
