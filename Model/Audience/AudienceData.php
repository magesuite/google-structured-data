<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Audience;

class AudienceData
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration,
        protected \MageSuite\GoogleStructuredData\Model\Audience\SuggestedGenderResolver $suggestedGenderResolver,
        protected \MageSuite\GoogleStructuredData\Model\Audience\SuggestedMinAgeResolver $suggestedMinAgeResolver
    ) {}

    public function getData(\Magento\Catalog\Api\Data\ProductInterface $product, int $storeId): array
    {
        if (!$this->productConfiguration->isAudienceEnabled($storeId)) {
            return [];
        }

        $suggestedGender = $this->suggestedGenderResolver->resolve($product, $storeId);
        $suggestedMinAge = $this->suggestedMinAgeResolver->resolve($product, $storeId);

        if (empty($suggestedGender) && empty($suggestedMinAge)) {
            return [];
        }

        return [
            'audience' => [
                '@type' => 'PeopleAudience',
                'suggestedGender' => $suggestedGender,
                'suggestedMinAge' => $suggestedMinAge
            ]
        ];
    }
}
