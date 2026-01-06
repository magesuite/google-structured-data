<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Config\Source;

class ReturnMethod implements \Magento\Framework\Data\OptionSourceInterface
{
    public const KEEP_PRODUCT = 'KeepProduct';
    public const RETURN_AT_KIOSK = 'ReturnAtKiosk';
    public const RETURN_BY_MAIL = 'ReturnByMail';
    public const RETURN_IN_STORE = 'ReturnInStore';

    public function toOptionArray(): array
    {
        return [
            ['value' => '', 'label' => __('-- Please Select --')],
            ['value' => self::KEEP_PRODUCT, 'label' => __('Keep Product')],
            ['value' => self::RETURN_AT_KIOSK, 'label' => __('Return At Kiosk')],
            ['value' => self::RETURN_BY_MAIL, 'label' => __('Return By Mail')],
            ['value' => self::RETURN_IN_STORE, 'label' => __('Return In Store')]
        ];
    }
}
