<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Config\Source;

class CategoryListItemMode implements \Magento\Framework\Data\OptionSourceInterface
{
    public const MODE_DISABLED = 0;
    public const MODE_FULL_PRODUCT_DATA = 1;
    public const MODE_PRODUCT_URLS = 2;

    public function toOptionArray(): array
    {
        return [
            ['value' => self::MODE_DISABLED, 'label' => __('Disabled')],
            ['value' => self::MODE_PRODUCT_URLS, 'label' => __('Product URLs only')],
            ['value' => self::MODE_FULL_PRODUCT_DATA, 'label' => __('Full product data')]
        ];
    }
}
