<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Indexer\Product;

class IndexDataGenerator
{
    public function __construct(
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
        protected \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider,
        protected \MageSuite\GoogleStructuredData\Model\Review\BatchReviewData $batchReviewData,
        protected \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {}

    public function generate(int $storeId, \Magento\Catalog\Model\ResourceModel\Product\Collection $products): array
    {
        $store = $this->storeManager->getStore($storeId);
        $originalStore = $this->storeManager->getStore();
        $this->storeManager->setCurrentStore($store);

        try {
            return $this->generateForStore($store, $products);
        } finally {
            $this->storeManager->setCurrentStore($originalStore);
        }
    }

    protected function generateForStore(\Magento\Store\Api\Data\StoreInterface $store, \Magento\Catalog\Model\ResourceModel\Product\Collection $products): array
    {
        $storeId = (int)$store->getId();
        $generatedData = [];

        $this->batchReviewData->load($products->getColumnValues('entity_id'), $storeId);

        foreach ($products as $product) {
            $productData = $this->productDataProvider->generateProductData($product, $store);

            if (empty($productData)) {
                continue;
            }

            $generatedData[] = [
                'product_id' => (int)$product->getId(),
                'store_id' => $storeId,
                'data' => $this->serializer->serialize($productData)
            ];
        }

        $this->batchReviewData->reset();

        return $generatedData;
    }
}
