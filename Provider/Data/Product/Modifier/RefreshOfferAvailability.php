<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data\Product\Modifier;

class RefreshOfferAvailability implements \MageSuite\GoogleStructuredData\Provider\Data\Product\ModifierInterface
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Model\ResourceModel\Product\InventoryData $inventoryData,
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration
    ) {}

    public function isEnabled(): bool
    {
        return true;
    }

    public function execute(array $productData, \Magento\Catalog\Api\Data\ProductInterface $product, \Magento\Store\Api\Data\StoreInterface $store): array
    {
        if (!$this->productConfiguration->isOfferAvailabilityRefreshEnabled((int)$store->getId())) {
            return $productData;
        }

        $salableStatuses = $this->inventoryData->getSalableStatusesBySku(array_values(array_unique($this->getOfferSkus($productData))), (int)$store->getId());

        if (empty($salableStatuses)) {
            return $productData;
        }

        return $this->applySalableStatuses($productData, $salableStatuses);
    }

    protected function getOfferSkus(array $data): array
    {
        $offerSkus = $this->isOffer($data) ? [$this->getOfferSku($data)] : [];
        $nestedOfferSkus = array_map(fn (array $value): array => $this->getOfferSkus($value), array_values(array_filter($data, 'is_array')));

        return array_merge($offerSkus, ...$nestedOfferSkus);
    }

    protected function applySalableStatuses(array $data, array $salableStatuses): array
    {
        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                continue;
            }

            $data[$key] = $this->applySalableStatuses($value, $salableStatuses);
        }

        if (!$this->isOffer($data) || !isset($salableStatuses[$this->getOfferSku($data)])) {
            return $data;
        }

        $data['availability'] = $this->getAvailability($salableStatuses[$this->getOfferSku($data)]);

        return $data;
    }

    protected function isOffer(array $data): bool
    {
        return ($data['@type'] ?? null) === 'Offer' && is_string($data['sku'] ?? null);
    }

    protected function getOfferSku(array $offer): string
    {
        return htmlspecialchars_decode($offer['sku'], ENT_QUOTES | ENT_SUBSTITUTE);
    }

    protected function getAvailability(bool $isSalable): string
    {
        $availability = $isSalable
            ? \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolver\DefaultResolver::IN_STOCK
            : \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolver\DefaultResolver::OUT_OF_STOCK;

        return sprintf('%s%s', \MageSuite\GoogleStructuredData\Provider\Data\Product\TypeResolverInterface::CONTEXT, $availability);
    }
}
