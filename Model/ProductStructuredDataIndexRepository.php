<?php

namespace MageSuite\GoogleStructuredData\Model;

class ProductStructuredDataIndexRepository
{
    protected array $productsStructuredData = [];

    protected \Magento\Framework\Serialize\SerializerInterface $serializer;
    protected \MageSuite\GoogleStructuredData\Model\ResourceModel\Index $indexResourceModel;
    protected \Psr\Log\LoggerInterface $logger;

    public function __construct(
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \MageSuite\GoogleStructuredData\Model\ResourceModel\Index $indexResourceModel,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->serializer = $serializer;
        $this->indexResourceModel = $indexResourceModel;
        $this->logger = $logger;
    }

    public function loadDataFromIndex(array $productIds, int $storeId): void
    {
        $productsData = $this->indexResourceModel->getByProductIdsAndStoreId($productIds, $storeId);

        foreach ($productsData as $productId => $productData) {
            try {
                $productData = $this->serializer->unserialize($productData);
            } catch (\Exception $e) {
                $this->logger->error('There has been an error during loading structured data from index: ' . $e->getMessage());
                $productData = [];
            }

            $this->productsStructuredData[$storeId][$productId] = $productData;
        }
    }

    public function getDataFromIndex(int $productId, int $storeId): array
    {
        if (array_key_exists($storeId, $this->productsStructuredData) && array_key_exists($productId, $this->productsStructuredData[$storeId])) {
            return $this->productsStructuredData[$storeId][$productId];
        }

        $this->loadDataFromIndex([$productId], $storeId);

        return $this->productsStructuredData[$storeId][$productId];
    }
}
