<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Test\Integration\Stub;

class BrandSourceModel extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    public const FIRST_BRAND_ID = 9201;
    public const SECOND_BRAND_ID = 9202;

    public function getAllOptions(): array
    {
        return [
            ['value' => (string)self::FIRST_BRAND_ID, 'label' => 'First Brand'],
            ['value' => (string)self::SECOND_BRAND_ID, 'label' => 'Second Brand'],
        ];
    }
}
