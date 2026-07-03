<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Eav;

class BatchAttributeOptionData
{
    protected array $optionLabels = [];

    protected array $loaded = [];

    protected array $attributeIdCache = [];

    public function __construct(
        protected \Magento\Framework\App\ResourceConnection $resourceConnection,
        protected \Magento\Eav\Model\Config $eavConfig
    ) {}

    public function getOptionText(string $attributeCode, int $storeId, mixed $value): string|array|null
    {
        $optionIds = $this->normalizeValue($value);

        if (empty($optionIds)) {
            return null;
        }

        $attributeId = $this->getAttributeId($attributeCode);

        if (!$attributeId) {
            return null;
        }

        $this->ensureLoaded($attributeId, $storeId);
        $labels = $this->optionLabels[$storeId][$attributeId] ?? [];

        $resolved = [];

        foreach ($optionIds as $optionId) {
            if (!isset($labels[$optionId])) {
                continue;
            }

            $resolved[] = $labels[$optionId];
        }

        if (empty($resolved)) {
            return null;
        }

        return $this->isMultiple($value) ? $resolved : $resolved[0];
    }

    protected function normalizeValue(mixed $value): array
    {
        if ($value === null || $value === '' || $value === false) {
            return [];
        }

        if (is_array($value)) {
            return array_values(array_filter(array_map('intval', $value)));
        }

        return array_values(array_filter(array_map('intval', explode(',', (string)$value))));
    }

    protected function isMultiple(mixed $value): bool
    {
        if (is_array($value)) {
            return true;
        }

        return is_string($value) && str_contains($value, ',');
    }

    protected function getAttributeId(string $attributeCode): int
    {
        if (isset($this->attributeIdCache[$attributeCode])) {
            return $this->attributeIdCache[$attributeCode];
        }

        $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
        $this->attributeIdCache[$attributeCode] = (int)$attribute->getId();

        return $this->attributeIdCache[$attributeCode];
    }

    protected function ensureLoaded(int $attributeId, int $storeId): void
    {
        if (isset($this->loaded[$storeId][$attributeId])) {
            return;
        }

        $this->loaded[$storeId][$attributeId] = true;
        $this->optionLabels[$storeId][$attributeId] = $this->fetchOptionLabels($attributeId, $storeId);
    }

    protected function fetchOptionLabels(int $attributeId, int $storeId): array
    {
        $connection = $this->resourceConnection->getConnection();
        $optionTable = $this->resourceConnection->getTableName('eav_attribute_option');
        $valueTable = $this->resourceConnection->getTableName('eav_attribute_option_value');

        $select = $connection->select()
            ->from(['option' => $optionTable], ['option_id'])
            ->joinInner(
                ['default_value' => $valueTable],
                'default_value.option_id = option.option_id AND default_value.store_id = ' . \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                []
            )
            ->joinLeft(
                ['store_value' => $valueTable],
                $connection->quoteInto(
                    'store_value.option_id = option.option_id AND store_value.store_id = ?',
                    $storeId
                ),
                ['value' => new \Magento\Framework\DB\Sql\Expression('IF(store_value.value IS NULL, default_value.value, store_value.value)')]
            )
            ->where('option.attribute_id = ?', $attributeId);

        return $connection->fetchPairs($select);
    }
}
