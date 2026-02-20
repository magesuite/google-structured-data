<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Indexer\Product;

class DataProvider
{
    protected const DEPLOYMENT_CONFIG_INDEXER_BATCHES = 'indexer/batch_size/';

    protected ?array $attributeList = null;

    public function __construct(
        protected \Magento\Catalog\Model\Config $catalogConfig,
        protected \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        protected \MageSuite\GoogleStructuredData\Model\ResourceModel\Product\InventoryData $inventoryData,
        protected \MageSuite\GoogleStructuredData\Provider\Data\Product\CompositeAttribute $compositeAttributeProvider,
        protected \Magento\Framework\Event\ManagerInterface $eventManager,
        protected \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        protected int $batchSize = 1000
    ) {}

    public function getProducts(
        array $dimensions,
        ?array $productIds = null,
        int $lastProductId = 0
    ): \Magento\Catalog\Model\ResourceModel\Product\Collection
    {
        $storeId = (int)$dimensions[\Magento\Store\Model\StoreDimensionProvider::DIMENSION_NAME]->getValue();
        $collection = $this->productCollectionFactory->create();
        $collection->addStoreFilter($storeId);
        $collection->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $collection->addAttributeToFilter('visibility', ['neq' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE]);
        $collection->addAttributeToSelect($this->getAttributeList());
        $this->inventoryData->addStockDataToCollection($collection, $storeId);

        if (!empty($productIds)) {
            $collection->addIdFilter($productIds);
        } else {
            $collection->setPageSize($this->getBatchSize());
        }

        if ($lastProductId > 0) {
            $collection->addFieldToFilter('entity_id', ['gt' => $lastProductId]);
        }

        $this->eventManager->dispatch(
            'product_structured_index_collection_before_load',
            ['collection' => $collection]
        );

        $collection->addUrlRewrite();
        $collection->addMediaGalleryData();

        return $collection;
    }

    public function getAttributeList(): array
    {
        if ($this->attributeList !== null) {
            return $this->attributeList;
        }

        $this->attributeList = array_unique(array_merge(
            $this->compositeAttributeProvider->getEavAttributeCodes(),
            $this->catalogConfig->getProductAttributes()
        ));

        return $this->attributeList;
    }

    public function getBatchSize(): int
    {
        $batchSize = (int)$this->deploymentConfig->get(
            self::DEPLOYMENT_CONFIG_INDEXER_BATCHES . \MageSuite\GoogleStructuredData\Model\Indexer\Product\Processor::INDEXER_ID
        );

        return $batchSize > 0 ? $batchSize : $this->batchSize;
    }
}
