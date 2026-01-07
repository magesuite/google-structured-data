<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data\Product\Modifier;

class Audience implements \MageSuite\GoogleStructuredData\Provider\Data\Product\ModifierInterface
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $configuration,
        protected bool $isEnabled = true
    ) {}

    public function execute(array $productData, \Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store): array
    {
        if (!$this->isEnabled()) {
            return $productData;
        }

        $audienceData = $this->getAudienceData($store);

        if (!empty($audienceData)) {
            $productData['audience'] = $audienceData;
        }

        return $productData;
    }

    public function getAudienceData(\Magento\Store\Api\Data\StoreInterface $store): ?array
    {
        if (!$this->configuration->isAudienceEnabled((int)$store->getId())) {
            return null;
        }

        $suggestedGender = $this->configuration->getAudienceSuggestedGender((int)$store->getId());
        $suggestedAge = $this->configuration->getAudienceSuggestedMinAge((int)$store->getId());

        if (empty($suggestedGender) && empty($suggestedAge)) {
            return null;
        }

        return [
            '@type' => 'PeopleAudience',
            'suggestedGender' => $suggestedGender,
            'suggestedMinAge' => $suggestedAge
        ];
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }
}
