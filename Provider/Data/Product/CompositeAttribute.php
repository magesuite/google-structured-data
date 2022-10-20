<?php

namespace MageSuite\GoogleStructuredData\Provider\Data\Product;

class CompositeAttribute
{
    const ATTRIBUTE_EAV = 'eav';
    const ATTRIBUTE_CONFIGURED_EAV = 'configured_eav';
    const ATTRIBUTE_CUSTOM = 'custom';

    protected \Magento\Framework\Escaper $escaper;

    protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration;

    protected array $attributeDataProviders;

    protected array $eavAttributeCodes = [];

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
                return (!isset($item['disabled']) || !$item['disabled']) && $item['class'] && $item['type'];
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

            if (!$attributeValue) {
                continue;
            }

            if (is_array($attributeValue)) {
                foreach ($attributeValue as $index => $value) {
                    $attributeData[$attributeKey][$index] = $this->escaper->escapeHtml($value);
                }
            } else {
                $attributeData[$attributeKey] = $this->escaper->escapeHtml($attributeValue);
            }
        }

        return $attributeData;
    }

    public function getEavAttributeCodes(): array
    {
        if (!empty($this->eavAttributeCodes)) {
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
