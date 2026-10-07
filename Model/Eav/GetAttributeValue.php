<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Eav;

class GetAttributeValue
{
    protected array $attributeTextTypes = ['select', 'multiselect'];
    protected array $attributeInputTypeCache = [];
    protected array $attributeUsesOptionTableCache = [];
    protected array $sourceOptionTextCache = [];

    public function __construct(
        protected \Magento\Eav\Model\Config $eavConfig,
        protected \MageSuite\GoogleStructuredData\Model\Eav\BatchAttributeOptionData $batchAttributeOptionData
    ) {}

    public function execute(\Magento\Catalog\Api\Data\ProductInterface $product, string $attributeCode): string|array|null
    {
        if (!$this->isTextAttribute($attributeCode)) {
            $value = $product->getData($attributeCode);

            return $value !== null ? (string) $value : null;
        }

        $value = $this->usesOptionTable($attributeCode) ? $this->getOptionTableText($product, $attributeCode) : $this->getSourceModelText($product, $attributeCode);

        return $this->normalizeText($value);
    }

    protected function isTextAttribute(string $attributeCode): bool
    {
        if (!isset($this->attributeInputTypeCache[$attributeCode])) {
            $this->attributeInputTypeCache[$attributeCode] = $this->getAttribute($attributeCode)->getFrontendInput();
        }

        return in_array($this->attributeInputTypeCache[$attributeCode], $this->attributeTextTypes);
    }

    protected function usesOptionTable(string $attributeCode): bool
    {
        if (!isset($this->attributeUsesOptionTableCache[$attributeCode])) {
            $sourceModel = (string)$this->getAttribute($attributeCode)->getSourceModel();
            $this->attributeUsesOptionTableCache[$attributeCode] = $sourceModel === '' || is_a($sourceModel, \Magento\Eav\Model\Entity\Attribute\Source\Table::class, true);
        }

        return $this->attributeUsesOptionTableCache[$attributeCode];
    }

    protected function getAttribute(string $attributeCode): \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
    {
        return $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
    }

    protected function getOptionTableText(\Magento\Catalog\Api\Data\ProductInterface $product, string $attributeCode): string|array|null
    {
        return $this->batchAttributeOptionData->getOptionText($attributeCode, (int)$product->getStoreId(), $product->getData($attributeCode));
    }

    protected function getSourceModelText(\Magento\Catalog\Api\Data\ProductInterface $product, string $attributeCode): mixed
    {
        $value = $product->getData($attributeCode);

        if ($this->isEmptyValue($value)) {
            return null;
        }

        $attribute = $this->getAttribute($attributeCode);
        $originalStoreId = $attribute->getStoreId();
        $attribute->setStoreId((int)$product->getStoreId());

        try {
            return $attribute->getFrontendInput() === 'multiselect' ? $this->getSourceOptionTexts($attribute, $value) : $this->getSourceOptionText($attribute, $value);
        } finally {
            $attribute->setStoreId($originalStoreId);
        }
    }

    protected function getSourceOptionTexts(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute, mixed $value): array
    {
        $optionIds = is_array($value) ? $value : explode(',', (string)$value);
        $optionTexts = array_map(
            fn($optionId) => $this->getSourceOptionText($attribute, $optionId),
            $optionIds
        );

        return array_values(array_filter($optionTexts, fn($optionText) => !$this->isEmptyValue($optionText)));
    }

    protected function getSourceOptionText(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute, mixed $optionId): mixed
    {
        $attributeCode = $attribute->getAttributeCode();
        $storeId = (int)$attribute->getStoreId();
        $optionKey = (string)$optionId;

        if (!array_key_exists($optionKey, $this->sourceOptionTextCache[$attributeCode][$storeId] ?? [])) {
            $this->sourceOptionTextCache[$attributeCode][$storeId][$optionKey] = $this->loadSourceOptionText($attribute, $optionId);
        }

        return $this->sourceOptionTextCache[$attributeCode][$storeId][$optionKey];
    }

    protected function loadSourceOptionText(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute, mixed $optionId): mixed
    {
        $optionText = $attribute->getSource()->getOptionText($optionId);

        return $this->isOptionRecord($optionText) ? null : $optionText;
    }

    protected function isOptionRecord(mixed $optionText): bool
    {
        return is_array($optionText) && array_key_exists('value', $optionText);
    }

    protected function normalizeText(mixed $value): string|array|null
    {
        if ($this->isEmptyValue($value)) {
            return null;
        }

        if (is_array($value)) {
            return array_map('strval', $value);
        }

        return (string) $value;
    }

    protected function isEmptyValue(mixed $value): bool
    {
        return in_array($value, [null, false, '', []], true);
    }
}
