<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Config\Source;

class VariesByOptions implements \Magento\Framework\Data\OptionSourceInterface
{
    public function __construct(
        protected array $variesByTypes = []
    ) {}

    public function toOptionArray(): array
    {
        $options = [['value' => '', 'label' => ' ']];
        foreach ($this->variesByTypes as $type) {
            $options[] = ['value' => $type, 'label' => $type];
        }

        return $options;
    }
}
