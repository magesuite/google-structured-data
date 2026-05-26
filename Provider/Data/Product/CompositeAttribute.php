<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data\Product;

class CompositeAttribute
{
    public const ATTRIBUTE_EAV = 'eav';
    public const ATTRIBUTE_CONFIGURED_EAV = 'configured_eav';
    public const ATTRIBUTE_CUSTOM = 'custom';
    public const ATTRIBUTE_PRELOAD_ONLY = 'preload_only';

    protected array $attributeDataProviders = [];
    protected array $eavAttributeCodes = [];

    public function __construct(
        protected \Magento\Framework\Escaper $escaper,
        protected \Magento\Eav\Model\Config $eavConfig,
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration,
        array $attributeDataProviders = []
    ) {
        $this->attributeDataProviders = array_filter(
            $attributeDataProviders,
            function ($item): bool {
                return (!isset($item['disabled']) || !$item['disabled'])
                    && !empty($item['type'])
                    && (($item['class'] ?? null) || $item['type'] === self::ATTRIBUTE_PRELOAD_ONLY);
            }
        );
    }

    public function getAttributeData(\Magento\Catalog\Api\Data\ProductInterface $product, ?int $storeId = null): array
    {
        $attributeData = [];

        foreach ($this->attributeDataProviders as $attributeKey => $attributeDataProvider) {
            if ($attributeDataProvider['type'] === self::ATTRIBUTE_PRELOAD_ONLY) {
                continue;
            }

            $providerClass = $attributeDataProvider['class'];
            if (!$providerClass instanceof \MageSuite\GoogleStructuredData\Provider\Data\Product\AttributeInterface) {
                continue;
            }

            $attribute = $attributeDataProvider['attribute_name'] ?? null;
            $eavAttributeCodes = $this->getEavAttributeCodes($storeId);

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

            $this->addEscapedAttributeValue($attributeData, $attributeKey, $attributeValue);
        }

        return $attributeData;
    }

    protected function addEscapedAttributeValue(array &$attributeData, string $attributeKey, mixed $attributeValue): void
    {
        if (is_array($attributeValue)) {
            foreach ($attributeValue as $index => $value) {
                $attributeData[$attributeKey][$index] = $this->escaper->escapeHtml($value);
            }
            return;
        }

        $attributeData[$attributeKey] = $this->escaper->escapeHtml($attributeValue);
    }

    public function getEavAttributeCodes(?int $storeId = null): array
    {
        $cacheKey = $storeId ?? 0;

        if (isset($this->eavAttributeCodes[$cacheKey])) {
            return $this->eavAttributeCodes[$cacheKey];
        }

        $this->eavAttributeCodes[$cacheKey] = [];

        foreach ($this->attributeDataProviders as $attributeKey => $attributeDataProvider) {
            if (!in_array($attributeDataProvider['type'], [self::ATTRIBUTE_EAV, self::ATTRIBUTE_CONFIGURED_EAV, self::ATTRIBUTE_PRELOAD_ONLY])) {
                continue;
            }

            if ($attributeDataProvider['type'] === self::ATTRIBUTE_EAV) {
                $attributeCode = $attributeDataProvider['attribute_name'];
            } elseif (in_array($attributeDataProvider['type'], [self::ATTRIBUTE_CONFIGURED_EAV, self::ATTRIBUTE_PRELOAD_ONLY])) {
                $attributeCode = isset($attributeDataProvider['config_path'])
                    ? $this->productConfiguration->getAttributeByConfigPath($attributeDataProvider['config_path'], $storeId)
                    : $this->productConfiguration->getConfiguredAttribute($attributeDataProvider['attribute_name'], $storeId);
            }

            if (!$attributeCode) {
                continue;
            }

            $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
            if (!$attribute->getId()) {
                continue;
            }

            $this->eavAttributeCodes[$cacheKey][$attributeKey] = $attributeCode;
        }

        return $this->eavAttributeCodes[$cacheKey];
    }
}
