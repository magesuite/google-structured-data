<?php
namespace MageSuite\GoogleStructuredData\Observer;

class AddStructuredDataToProduct implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageSuite\GoogleStructuredData\Provider\StructuredDataProvider
     */
    private $structuredDataProvider;
    /**
     * @var \MageSuite\GoogleStructuredData\Provider\Data\Product
     */
    private $productDataProvider;

    public function __construct(
        \MageSuite\GoogleStructuredData\Provider\StructuredDataProvider $structuredDataProvider,
        \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider
    )
    {
        $this->structuredDataProvider = $structuredDataProvider;
        $this->productDataProvider = $productDataProvider;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $productData = $this->productDataProvider->getProductStructuredData();

        $structuredDataProvider = $this->structuredDataProvider;

        $structuredDataProvider->add($productData, $structuredDataProvider::PRODUCT);
    }
}