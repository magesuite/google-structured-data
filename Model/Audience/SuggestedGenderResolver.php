<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Audience;

class SuggestedGenderResolver
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration,
        protected \MageSuite\GoogleStructuredData\Model\Eav\GetAttributeValue $getAttributeValue
    ) {}

    public function resolve(\Magento\Catalog\Api\Data\ProductInterface $product, int $storeId): ?string
    {
        $attributeCode = $this->productConfiguration->getAudienceSuggestedGenderAttribute($storeId);

        if (!$attributeCode) {
            return $this->productConfiguration->getAudienceSuggestedGender($storeId);
        }

        $result = $this->getAttributeValue->execute($product, $attributeCode);
        $values = is_array($result) ? $result : [$result];

        $validValues = [
            \MageSuite\GoogleStructuredData\Model\Config\Source\AudienceSuggestedGender::MALE,
            \MageSuite\GoogleStructuredData\Model\Config\Source\AudienceSuggestedGender::FEMALE,
            \MageSuite\GoogleStructuredData\Model\Config\Source\AudienceSuggestedGender::UNISEX,
        ];

        $matchedValues = array_filter(
            array_map(fn($v) => strtolower((string)$v), $values),
            fn($v) => in_array($v, $validValues, true)
        );

        if (empty($matchedValues)) {
            return $this->productConfiguration->getAudienceSuggestedGender($storeId);
        }

        if (count($matchedValues) > 1) {
            return \MageSuite\GoogleStructuredData\Model\Config\Source\AudienceSuggestedGender::UNISEX;
        }

        return reset($matchedValues);
    }
}
