<?php

namespace MageSuite\GoogleStructuredData\Provider\Data\Product;

class CompositeAttribute
{
    /**
     * @var \Magento\Framework\Escaper
     */
    protected \Magento\Framework\Escaper $escaper;

    /**
     * @var AttributeInterface[]
     */
    protected $attributeDataProviders;

    public function __construct(
        \Magento\Framework\Escaper $escaper,
        array $attributeDataProviders = []
    ) {
        $this->escaper = $escaper;

        $this->attributeDataProviders = array_filter(
            $attributeDataProviders,
            function ($item) {
                return (!isset($item['disable']) || !$item['disable']) && $item['class'];
            }
        );
    }

    public function getAttributeData(\Magento\Catalog\Api\Data\ProductInterface $product): array
    {
        $attributeData = [];

        foreach ($this->attributeDataProviders as $attributeKey => $attributeDataProvider) {
            $providerClass = $attributeDataProvider['class'];
            if (!$providerClass instanceof \MageSuite\GoogleStructuredData\Provider\Data\Product\AttributeInterface) {
                continue;
            }

            try {
                $attributeValue = $providerClass->getAttributeData($product, $attributeDataProvider['attribute']);
            } catch (\Exception $e) {
                $attributeValue = null;
            }

            if ($attributeValue) {
                $attributeData[$attributeKey] = $this->escaper->escapeHtml($attributeValue);
            }
        }

        return $attributeData;
    }
}
