<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Eav;

class StructuredDataDescriptionResolver
{
    protected array $descriptions = [];

    public function __construct(
        protected \Magento\Framework\Serialize\SerializerInterface $serializer,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
        protected \Psr\Log\LoggerInterface $logger
    ) {}

    public function resolve(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute, int $storeId): ?string
    {
        $cacheKey = sprintf('%s_%s', $attribute->getAttributeCode(), $storeId);

        if (!array_key_exists($cacheKey, $this->descriptions)) {
            $this->descriptions[$cacheKey] = $this->getDescription($attribute, $storeId);
        }

        return $this->descriptions[$cacheKey];
    }

    protected function getDescription(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute, int $storeId): ?string
    {
        $description = trim((string)$attribute->getData('structured_data_description'));

        if ($description === '') {
            return null;
        }

        if (!str_starts_with($description, '{')) {
            return $description;
        }

        $descriptionsByStoreCode = $this->getDescriptionsByStoreCode($attribute, $description);
        $store = $this->storeManager->getStore($storeId);
        $storeDescription = $descriptionsByStoreCode[$store->getCode()] ?? $descriptionsByStoreCode[$this->getDefaultStoreCode($store)] ?? null;

        return is_string($storeDescription) && trim($storeDescription) !== '' ? trim($storeDescription) : null;
    }

    protected function getDescriptionsByStoreCode(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute, string $description): array
    {
        try {
            $descriptionsByStoreCode = $this->serializer->unserialize($description);
        } catch (\InvalidArgumentException $exception) {
            $this->logger->warning(sprintf('Invalid structured data description of the "%s" attribute: %s', $attribute->getAttributeCode(), $exception->getMessage()));

            return [];
        }

        return is_array($descriptionsByStoreCode) ? $descriptionsByStoreCode : [];
    }

    protected function getDefaultStoreCode(\Magento\Store\Api\Data\StoreInterface $store): string
    {
        $defaultStore = $this->storeManager->getWebsite($store->getWebsiteId())->getDefaultStore();

        return $defaultStore ? (string)$defaultStore->getCode() : '';
    }
}
