<?php

namespace MageSuite\GoogleStructuredData\Plugin;

// phpcs:ignoreFile
class RemoveSchemaInBlocks
{
    public function aroundGetData(\Magento\Framework\View\Element\Template $subject, callable $proceed, $key = '', $index = null)
    {
        /**
         * Logic is added to avoid duplicating Magento Schema.org data with data provided in this module
         */
        if ($key != 'schema') {
            return $proceed($key, $index);
        }

        return false;
    }
}
