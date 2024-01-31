<?php

namespace MageSuite\GoogleStructuredData\Provider\Data\Product\DeliveryData;

class HandlingTime
{
    protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $configuration;

    public function __construct(
        \MageSuite\GoogleStructuredData\Helper\Configuration\Product $configuration
    ) {
        $this->configuration = $configuration;
    }

    public function getHandlingTimeData(\Magento\Framework\DataObject $data): array
    {
        $storeId = $data->getStore() ? $data->getStore()->getId() : 0;
        $handlingTimeValue = $this->configuration->getHandlingTime($storeId);
        $handlingTimeUnit = $this->configuration->getHandlingTimeUnit($storeId);

        if (!$handlingTimeValue || !$handlingTimeUnit) {
            return [];
        }

        $handlingTime = $this->getHandlingTimeValue($handlingTimeValue);

        if (!$handlingTime) {
            return [];
        }

        $handlingTimeData = array_merge(["@type" => "QuantitativeValue"], $handlingTime);
        $handlingTimeData['unitCode'] = $handlingTimeUnit;

        return ['handlingTime' => $handlingTimeData];
    }

    public function getHandlingTimeValue(string $handlingTime): array
    {
        $parts = explode('-', $handlingTime);

        $handlingTimeValue = [];

        if (count($parts) == 1) {
            $handlingTimeValue = ['value' => $parts[0]];
        }

        if (count($parts) == 2) {
            $handlingTimeValue = [
                'minValue' => $parts[0],
                'maxValue' => $parts[1]
            ];
        }

        return  $handlingTimeValue;
    }
}
