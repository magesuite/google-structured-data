<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Audience;

class SuggestedMinAgeResolver
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration,
        protected \MageSuite\GoogleStructuredData\Model\Eav\GetAttributeValue $getAttributeValue
    ) {}

    public function resolve(\Magento\Catalog\Api\Data\ProductInterface $product, int $storeId): float
    {
        $attributeCode = $this->productConfiguration->getAudienceSuggestedMinAgeAttribute($storeId);

        if (!$attributeCode) {
            return (float)$this->productConfiguration->getAudienceSuggestedMinAge($storeId);
        }

        $attributeValue = $this->getAttributeValue->execute($product, $attributeCode);
        $rawValue = (string)(is_array($attributeValue) ? reset($attributeValue) : $attributeValue);
        $result = $rawValue !== '' ? $rawValue : $this->productConfiguration->getAudienceSuggestedMinAge($storeId);

        return (float)$result;
    }
}
