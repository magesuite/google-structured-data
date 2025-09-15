<?php

namespace MageSuite\GoogleStructuredData\Model\ResourceModel;

class ProductPrice
{
    protected ?\Magento\Framework\DB\Adapter\AdapterInterface $connection;
    protected ?array $priceDataCache = null;

    public function __construct(\Magento\Framework\App\ResourceConnection $resourceConnection)
    {
        $this->connection = $resourceConnection->getConnection();
    }

    public function getProductPrice(int $productId, int $websiteId): ?array
    {
        if ($this->priceDataCache === null || !isset($this->priceDataCache[$websiteId])) {
            $select = $this->connection->select()
                ->from(['p' => $this->connection->getTableName('catalog_product_index_price')], ['entity_id', 'price', 'final_price'])
                ->where('p.customer_group_id = ?', \Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID)
                ->where('p.website_id = ?', $websiteId);
            $this->priceDataCache[$websiteId] = $this->connection->fetchAssoc($select);
        }

        return $this->priceDataCache[$websiteId][$productId] ?? null;
    }
}
