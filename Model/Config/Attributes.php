<?php

namespace MageSuite\GoogleStructuredData\Model\Config;

class Attributes implements \Magento\Framework\Option\ArrayInterface
{
    protected array $options = [];

    protected \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory;

    public function __construct(\Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    public function toOptionArray()
    {
        if (!empty($this->options)) {
            return $this->options;
        }

        $attributesCollection = $this->collectionFactory->create();
        $this->options = [['value' => 0, 'label' => __('--Please select--')]];

        foreach ($attributesCollection as $attribute) {
            $this->options[] = [
                'value' => $attribute->getAttributeCode(),
                'label' => sprintf(
                    '%s (%s)',
                    $attribute->getDefaultFrontendLabel(),
                    $attribute->getAttributeCode()
                )
            ];
        }

        return $this->options;
    }
}
