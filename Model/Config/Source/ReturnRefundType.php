<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Config\Source;

class ReturnRefundType implements \Magento\Framework\Data\OptionSourceInterface
{
    public const FULL_REFUND = 'FullRefund';
    public const STORE_CREDIT_REFUND = 'StoreCreditRefund';
    public const EXCHANGE_REFUND = 'ExchangeRefund';

    public function toOptionArray(): array
    {
        return [
            ['value' => '', 'label' => __('-- Please Select --')],
            ['value' => self::FULL_REFUND, 'label' => __('Full Refund')],
            ['value' => self::STORE_CREDIT_REFUND, 'label' => __('Store Credit Refund')],
            ['value' => self::EXCHANGE_REFUND, 'label' => __('Exchange Refund')]
        ];
    }
}
