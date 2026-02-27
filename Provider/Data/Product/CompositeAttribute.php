<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data\Product;

class CompositeAttribute
{
    public const ATTRIBUTE_EAV = 'eav';
    public const ATTRIBUTE_CONFIGURED_EAV = 'configured_eav';
    public const ATTRIBUTE_CUSTOM = 'custom';

    protected array $attributeDataProviders = [];
    protected ?array $eavAttributeCodes = null;

    public function __construct(
        protected \Magento\Framework\Escaper $escaper,
        protected \Magento\Eav\Model\Config $eavConfig,
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration,
        array $attributeDataProviders = []
    ) {
        $this->attributeDataProviders = array_filter(
            $attributeDataProviders,
            function ($item): bool {
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

            if (!isset($eavAttributeCodes[$attributeKey])) {
                continue;
            }

            if ($attributeDataProvider['type'] !== self::ATTRIBUTE_CUSTOM) {
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
        if ($this->eavAttributeCodes !== null) {
            return $this->eavAttributeCodes;
        }

        $this->eavAttributeCodes = [];

        foreach ($this->attributeDataProviders as $attributeKey => $attributeDataProvider) {
            if (!in_array($attributeDataProvider['type'], [self::ATTRIBUTE_EAV, self::ATTRIBUTE_CONFIGURED_EAV])) {
                continue;
            }

            if ($attributeDataProvider['type'] === self::ATTRIBUTE_EAV) {
                $attributeCode = $attributeDataProvider['attribute_name'];
            } elseif ($attributeDataProvider['type'] === self::ATTRIBUTE_CONFIGURED_EAV) {
                $attributeCode = $this->productConfiguration->getConfiguredAttribute($attributeDataProvider['attribute_name']);
            }

            $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
            if (!$attribute->getId()) {
                continue;
            }

            $this->eavAttributeCodes[$attributeKey] = $attributeCode;
        }

        return $this->eavAttributeCodes;
    }
}
