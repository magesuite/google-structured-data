<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Config\Source;

class ReturnFees implements \Magento\Framework\Data\OptionSourceInterface
{
    public const FREE_RETURN = 'FreeReturn';
    public const ORIGINAL_SHIPPING_FEES = 'OriginalShippingFees';
    public const RESTOCKING_FEES = 'RestockingFees';
    public const RETURN_FEES_CUSTOMER_RESPONSIBILITY = 'ReturnFeesCustomerResponsibility';
    public const RETURN_SHIPPING_FEES = 'ReturnShippingFees';

    public function toOptionArray(): array
    {
        return [
            ['value' => '', 'label' => __('-- Please Select --')],
            ['value' => self::FREE_RETURN, 'label' => __('Free Return')],
            ['value' => self::ORIGINAL_SHIPPING_FEES, 'label' => __('Original Shipping Fees')],
            ['value' => self::RESTOCKING_FEES, 'label' => __('Restocking Fees')],
            ['value' => self::RETURN_FEES_CUSTOMER_RESPONSIBILITY, 'label' => __('Return Fees Customer Responsibility')],
            ['value' => self::RETURN_SHIPPING_FEES, 'label' => __('Return Shipping Fees')]
        ];
    }
}
