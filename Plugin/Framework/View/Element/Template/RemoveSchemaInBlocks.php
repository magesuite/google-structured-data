<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Plugin\Framework\View\Element\Template;

class RemoveSchemaInBlocks
{
    public function aroundGetData(\Magento\Framework\View\Element\Template $subject, callable $proceed, $key = '', $index = null) // phpcs:ignore
    {
        /**
         * Logic is added to avoid duplicating Magento Schema.org data with data provided in this module
         */
        if ($key !== 'schema') {
            return $proceed($key, $index);
        }

        return false;
    }
}
