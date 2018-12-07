<?php
namespace MageSuite\GoogleStructuredData\Plugin;

class RemoveSchemaInBlocks
{
    public function aroundGetData(\Magento\Framework\View\Element\Template $subject, callable $proceed, $key = '', $index = null)
    {
        if ($key != 'schema') {
            return $proceed($key, $index);
        }

        return false;
    }
}
