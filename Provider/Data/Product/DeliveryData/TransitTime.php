<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data\Product\DeliveryData;

class TransitTime
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $configuration
    ) {}

    public function getTransitTimeData(\Magento\Framework\DataObject $data): array
    {
        $storeId = $data->getStore() ? (int)$data->getStore()->getId() : \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        $transitTimeValue = $this->configuration->getTransitTime($storeId);
        $transitTimeUnit = $this->configuration->getTransitTimeUnit($storeId);

        if (!$transitTimeValue || !$transitTimeUnit) {
            return [];
        }

        $transitTime = $this->getTransitTimeValue($transitTimeValue);

        if (!$transitTime) {
            return [];
        }

        $transitTimeData = array_merge(["@type" => "QuantitativeValue"], $transitTime);
        $transitTimeData['unitCode'] = $transitTimeUnit;

        return ['transitTime' => $transitTimeData];
    }

    public function getTransitTimeValue(string $transitTime): array
    {
        $parts = explode('-', $transitTime);

        $transitTimeValue = [];

        if (count($parts) === 1) {
            $transitTimeValue = [
                'minValue' => $parts[0],
                'maxValue' => $parts[0]
            ];
        }

        if (count($parts) === 2) {
            $transitTimeValue = [
                'minValue' => $parts[0],
                'maxValue' => $parts[1]
            ];
        }

        return $transitTimeValue;
    }
}
