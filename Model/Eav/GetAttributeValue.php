<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Eav;

class GetAttributeValue
{
    protected array $attributeTextTypes = ['select', 'multiselect'];
    protected array $attributeInputTypeCache = [];

    public function __construct(
        protected \Magento\Eav\Model\Config $eavConfig,
        protected \MageSuite\GoogleStructuredData\Model\Eav\BatchAttributeOptionData $batchAttributeOptionData
    ) {}

    public function execute(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        string $attributeCode
    ): string|array|null {
        if (!isset($this->attributeInputTypeCache[$attributeCode])) {
            $this->attributeInputTypeCache[$attributeCode] = $this->eavConfig
                ->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode)
                ->getFrontendInput();
        }

        if (in_array($this->attributeInputTypeCache[$attributeCode], $this->attributeTextTypes)) {
            $value = $this->batchAttributeOptionData->getOptionText(
                $attributeCode,
                (int)$product->getStoreId(),
                $product->getData($attributeCode)
            );

            if ($value === null) {
                return null;
            }

            if (is_array($value)) {
                return array_map('strval', $value);
            }

            return (string) $value;
        }

        $value = $product->getData($attributeCode);

        return $value !== null ? (string) $value : null;
    }
}
