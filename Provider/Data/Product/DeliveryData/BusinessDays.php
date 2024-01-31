<?php

namespace MageSuite\GoogleStructuredData\Provider\Data\Product\DeliveryData;

class BusinessDays
{
    protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $configuration;

    public function __construct(
        \MageSuite\GoogleStructuredData\Helper\Configuration\Product $configuration
    ) {
        $this->configuration = $configuration;
    }

    public function getBusinessDaysData(\Magento\Framework\DataObject $data): array
    {
        $storeId = $data->getStore() ? $data->getStore()->getId() : 0;
        $businessDays = $this->configuration->getBusinessDays($storeId);

        $mappedWeekDays = $this->getMappedWeekDays($businessDays);

        $schemaFormatWeekDays = [];
        foreach ($mappedWeekDays as $day) {
            $schemaFormatWeekDays[] = sprintf('%s%s', 'https://schema.org/', $day);
        }

        if (!$schemaFormatWeekDays) {
            return [];
        }

        return [
            'businessDays' => [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => $schemaFormatWeekDays
            ]
        ];
    }

    public function getMappedWeekDays(array $days): array
    {
        $weekDays = [];
        foreach ($days as $dayNumber) {
            $weekDays[] = jddayofweek($dayNumber - 1, CAL_DOW_LONG);
        }

        if (!empty($weekDays) && ($weekDays[0] == 'Sunday')) {
            array_push($weekDays, $weekDays[0]);
            unset($weekDays[0]);
        }

        return $weekDays;
    }
}
