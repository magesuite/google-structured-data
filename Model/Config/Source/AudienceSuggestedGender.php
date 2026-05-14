<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Config\Source;

class AudienceSuggestedGender implements \Magento\Framework\Data\OptionSourceInterface
{
    public const MALE = 'male';
    public const FEMALE = 'female';
    public const UNISEX = 'unisex';

    public function toOptionArray(): array
    {
        return [
            ['value' => '', 'label' => __('-- Please Select --')],
            ['value' => self::MALE, 'label' => __('Male')],
            ['value' => self::FEMALE, 'label' => __('Female')],
            ['value' => self::UNISEX, 'label' => __('Unisex')]
        ];
    }
}
