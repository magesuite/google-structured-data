<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data\Product\AdditionalProperty;

class FlaggedAttributes implements \MageSuite\GoogleStructuredData\Provider\Data\Product\AdditionalPropertyProviderInterface
{
    protected ?array $attributeCodes = null;

    public function __construct(
        protected \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory,
        protected \Magento\Eav\Model\Config $eavConfig,
        protected \MageSuite\GoogleStructuredData\Model\Eav\GetAttributeValue $getAttributeValue,
        protected \MageSuite\GoogleStructuredData\Model\Eav\StructuredDataDescriptionResolver $structuredDataDescriptionResolver
    ) {}

    public function getAttributeCodes(): array
    {
        if ($this->attributeCodes !== null) {
            return $this->attributeCodes;
        }

        $attributeCollection = $this->attributeCollectionFactory->create();
        $attributeCollection->addFieldToFilter('additional_table.use_in_structured_data', 1);
        $this->attributeCodes = $attributeCollection->getColumnValues('attribute_code');

        return $this->attributeCodes;
    }

    public function getAdditionalProperties(\Magento\Catalog\Api\Data\ProductInterface $product, int $storeId): array
    {
        $additionalProperties = [];

        foreach ($this->getAttributeCodes() as $attributeCode) {
            $additionalProperty = $this->getAdditionalProperty($product, $attributeCode, $storeId);

            if ($additionalProperty === null) {
                continue;
            }

            $additionalProperties[] = $additionalProperty;
        }

        return $additionalProperties;
    }

    protected function getAdditionalProperty(\Magento\Catalog\Api\Data\ProductInterface $product, string $attributeCode, int $storeId): ?array
    {
        $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
        $value = $this->getValue($product, $attribute);

        if ($value === null) {
            return null;
        }

        $additionalProperty = [
            '@type' => 'PropertyValue',
            'name' => (string)$attribute->getStoreLabel($storeId),
            'value' => $value
        ];
        $description = $this->structuredDataDescriptionResolver->resolve($attribute, $storeId);

        if ($description !== null) {
            $additionalProperty['description'] = $description;
        }

        return $additionalProperty;
    }

    protected function getValue(\Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute): string|bool|null
    {
        if ($attribute->getFrontendInput() === 'boolean') {
            return $this->getBooleanValue($product->getData($attribute->getAttributeCode()));
        }

        $value = $this->getAttributeValue->execute($product, $attribute->getAttributeCode());
        $value = is_array($value) ? implode(', ', $value) : (string)$value;
        $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8'); // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $value = trim((string)preg_replace('/[\s\x{00A0}]+/u', ' ', $value));

        return $value === '' ? null : $value;
    }

    protected function getBooleanValue(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (bool)(int)$value;
    }
}
