<?php

namespace MageSuite\GoogleStructuredData\Provider\Data\Product;

class CompositeAttribute
{
    const ATTRIBUTE_EAV = 'eav';
    const ATTRIBUTE_CONFIGURED_EAV = 'configured_eav';
    const ATTRIBUTE_CUSTOM = 'custom';

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \MageSuite\GoogleStructuredData\Helper\Configuration\Product
     */
    protected $productConfiguration;

    /**
     * @var AttributeInterface[]
     */
    protected $attributeDataProviders;

    /**
     * @var array
     */
    protected $eavAttributeCodes;

    public function __construct(
        \Magento\Framework\Escaper $escaper,
        \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration,
        array $attributeDataProviders = []
    ) {
        $this->escaper = $escaper;
        $this->productConfiguration = $productConfiguration;

        $this->attributeDataProviders = array_filter(
            $attributeDataProviders,
            function ($item) {
                return (!isset($item['disable']) || !$item['disable']) && $item['class'] && $item['type'];
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

            $attribute = $attributeDataProvider['attribute_name'] ?? null;
            $eavAttributeCodes = $this->getEavAttributeCodes();
            if ($attributeDataProvider['type'] != self::ATTRIBUTE_CUSTOM) {
                $attribute = $eavAttributeCodes[$attributeKey];
            }

            try {
                $attributeValue = $providerClass->getAttributeData($product, $attribute);
            } catch (\Exception $e) {
                $attributeValue = null;
            }

            if ($attributeValue) {
                $attributeData[$attributeKey] = $this->escaper->escapeHtml($attributeValue);
            }
        }

        return $attributeData;
    }

    public function getEavAttributeCodes()
    {
        if ($this->eavAttributeCodes) {
            return $this->eavAttributeCodes;
        }

        $eavAttributeCodes = [];
        foreach ($this->attributeDataProviders as $attributeKey => $attributeDataProvider) {
            if ($attributeDataProvider['type'] == self::ATTRIBUTE_EAV) {
                $eavAttributeCodes[$attributeKey] = $attributeDataProvider['attribute_name'];
            } elseif ($attributeDataProvider['type'] == self::ATTRIBUTE_CONFIGURED_EAV) {
                $eavAttributeCodes[$attributeKey] = $this->productConfiguration->getConfiguredAttribute($attributeDataProvider['attribute_name']);
            }
        }
        $this->eavAttributeCodes = $eavAttributeCodes;

        return $this->eavAttributeCodes;
    }
}
