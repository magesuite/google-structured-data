<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Catalog;

class BatchProductUrlData
{
    public function __construct(
        protected \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {}

    public function preloadForProducts(array $products, int $storeId): void
    {
        $productsToLoad = [];

        foreach ($products as $product) {
            if ($product->getData('request_path') !== null) {
                continue;
            }

            $productsToLoad[(int)$product->getId()] = $product;
        }

        if (empty($productsToLoad)) {
            return;
        }

        $requestPaths = $this->fetchRequestPaths(array_keys($productsToLoad), $storeId);

        foreach ($productsToLoad as $productId => $product) {
            $product->setData('request_path', $requestPaths[$productId] ?? false);
        }
    }

    protected function fetchRequestPaths(array $productIds, int $storeId): array
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()
            ->from(
                $this->resourceConnection->getTableName('url_rewrite'),
                ['entity_id', 'request_path']
            )
            ->where('entity_type = ?', \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator::ENTITY_TYPE)
            ->where('store_id = ?', $storeId)
            ->where('entity_id IN (?)', $productIds)
            ->where('redirect_type = ?', 0)
            ->where('metadata IS NULL');

        return $connection->fetchPairs($select);
    }
}
