<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Test\Integration\Stub;

class CustomSourceModel extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    public const OPTION_ID = 9101;

    public function __construct(
        protected \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {}

    public static function getOptionLabel(int $storeId): string
    {
        return sprintf('Custom Source Label for store %d', $storeId);
    }

    public function getAllOptions(): array
    {
        return [
            ['value' => '', 'label' => '-- Please Select --'],
            ['value' => self::OPTION_ID, 'label' => self::getOptionLabel($this->getStoreId())],
        ];
    }

    protected function getStoreId(): int
    {
        $storeId = $this->getAttribute()->getStoreId();

        if ($storeId === null) {
            return (int)$this->storeManager->getStore()->getId();
        }

        return (int)$storeId;
    }
}
