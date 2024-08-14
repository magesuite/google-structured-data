<?php

namespace MageSuite\GoogleStructuredData\Model\Config\Source;

class VariesByOptions implements \Magento\Framework\Option\ArrayInterface
{
    protected array $variesByTypes;

    public function __construct(array $variesByTypes = [])
    {
        $this->variesByTypes = $variesByTypes;
    }

    public function toOptionArray(): array
    {
        $options = [['value' => '', 'label' => ' ']];
        foreach ($this->variesByTypes as $type) {
            $options[] = ['value' => $type, 'label' => $type];
        }

        return $options;
    }
}
