<?php
namespace MageSuite\GoogleStructuredData\Model\Config;

class Attributes implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $collectionFactory;


    public function __construct(\Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get order period array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $attributesCollection = $this->collectionFactory->create();

        $attributes = [];

        $attributes[] = ['value' => 0, 'label' => '--Please select--'];

        foreach ($attributesCollection as $attribute) {
            $attributes[] = ['value' => $attribute->getAttributeCode(), 'label' => $attribute->getAttributeCode()];
        }

        return $attributes;
    }


}