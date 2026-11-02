<?php

declare(strict_types=1);

\Magento\TestFramework\Workaround\Override\Fixture\Resolver::getInstance()->requireDataFixture('Magento/GroupedProduct/_files/product_grouped.php');

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$reviewResource = $objectManager->get(\Magento\Review\Model\ResourceModel\Review::class);
$resourceConnection = $objectManager->get(\Magento\Framework\App\ResourceConnection::class);
$storeId = (int)$objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore()->getId();
$reviewsData = [
    [
        'sku' => 'grouped-product',
        'title' => 'Grouped product review',
        'created_at' => '2026-01-01 10:00:00'
    ],
    [
        'sku' => 'simple',
        'title' => 'Simple product review',
        'created_at' => '2026-01-03 10:00:00'
    ],
    [
        'sku' => 'virtual-product',
        'title' => 'Virtual product review',
        'created_at' => '2026-01-02 10:00:00'
    ]
];

foreach ($reviewsData as $reviewData) {
    $review = $objectManager->create(\Magento\Review\Model\Review::class);
    $review->setNickname('Nickname');
    $review->setTitle($reviewData['title']);
    $review->setDetail('Review text');
    $review->setEntityId($review->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE));
    $review->setEntityPkValue($productRepository->get($reviewData['sku'])->getId());
    $review->setStatusId(\Magento\Review\Model\Review::STATUS_APPROVED);
    $review->setStoreId($storeId);
    $review->setStores([$storeId]);
    $reviewResource->save($review);

    $resourceConnection->getConnection()->update($resourceConnection->getTableName('review'), ['created_at' => $reviewData['created_at']], ['review_id = ?' => $review->getId()]);
}
