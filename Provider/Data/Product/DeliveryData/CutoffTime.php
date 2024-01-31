<?php

namespace MageSuite\GoogleStructuredData\Provider\Data\Product\DeliveryData;

class CutoffTime
{
    protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration;

    protected \MageSuite\GoogleStructuredData\Helper\Configuration $configuration;

    public function __construct(
        \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration,
        \MageSuite\GoogleStructuredData\Helper\Configuration $configuration
    ) {
        $this->productConfiguration = $productConfiguration;
        $this->configuration = $configuration;
    }

    public function getCutoffTimeData(\Magento\Framework\DataObject $data): array
    {
        $store = $data->getStore();
        $storeId = $store ? $store->getId() : 0;
        $website = $store ? $store->getWebsite() : 0;

        $cutoffTimeValue = $this->productConfiguration->getCutoffTime($storeId);

        if (!$cutoffTimeValue) {
            return [];
        }

        $localeTimezone = $this->configuration->getTimezone($website);
        $timezone = new \DateTimeZone($localeTimezone);
        $cutoffDateTime = new \DateTime($cutoffTimeValue, $timezone);
        $cutoffDateTimeFormatted = $cutoffDateTime->format('c');
        $cutoffTime = substr($cutoffDateTimeFormatted, strpos($cutoffDateTimeFormatted, 'T') + 1);

        $data = ['cutoffTime' => $cutoffTime];

        return $data;
    }
}
