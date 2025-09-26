<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data\Product\DeliveryData;

class CutoffTime
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration,
        protected \MageSuite\GoogleStructuredData\Helper\Configuration $configuration
    ) {}

    public function getCutoffTimeData(\Magento\Framework\DataObject $data): array
    {
        $store = $data->getStore();
        $storeId = $store ? (int)$store->getId() : \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        $website = $store ? $store->getWebsite() : null;

        $cutoffTimeValue = $this->productConfiguration->getCutoffTime($storeId);

        if (!$cutoffTimeValue) {
            return [];
        }

        $localeTimezone = $this->configuration->getTimezone($website);
        $timezone = new \DateTimeZone($localeTimezone);
        $cutoffDateTime = new \DateTime($cutoffTimeValue, $timezone);
        $cutoffDateTimeFormatted = $cutoffDateTime->format('c');
        $cutoffTime = substr($cutoffDateTimeFormatted, strpos($cutoffDateTimeFormatted, 'T') + 1);

        return ['cutoffTime' => $cutoffTime];
    }
}
