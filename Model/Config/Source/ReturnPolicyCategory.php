<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Config\Source;

class ReturnPolicyCategory implements \Magento\Framework\Data\OptionSourceInterface
{
    public const FINITE_RETURN_WINDOW = 'MerchantReturnFiniteReturnWindow';
    public const NOT_PERMITTED = 'MerchantReturnNotPermitted';
    public const UNLIMITED_WINDOW = 'MerchantReturnUnlimitedWindow';
    public const UNSPECIFIED = 'MerchantReturnUnspecified';

    public function toOptionArray(): array
    {
        return [
            ['value' => self::FINITE_RETURN_WINDOW, 'label' => __('Finite Return Window')],
            ['value' => self::NOT_PERMITTED, 'label' => __('Not Permitted')],
            ['value' => self::UNLIMITED_WINDOW, 'label' => __('Unlimited Window')],
            ['value' => self::UNSPECIFIED, 'label' => __('Unspecified')],
        ];
    }
}
