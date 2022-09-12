<?php

namespace MageSuite\GoogleStructuredData\Model\Review;

class GetProductReviews
{
    protected \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory;

    public function __construct(\Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory)
    {
        $this->reviewCollectionFactory = $reviewCollectionFactory;
    }

    public function excute($product, $storeId)
    {
        $reviewsCollection = $this->reviewCollectionFactory->create();

        $reviewsCollection
            ->addStoreFilter($storeId)
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->addEntityFilter('product', $product->getId())
            ->setDateOrder()
            ->setPageSize(10);

        $reviewsCollection->getSelect()
            ->joinLeft(
                ['rov' => $reviewsCollection->getTable('rating_option_vote')],
                'main_table.review_id = rov.review_id',
                ['percent']
            )->group('main_table.review_id');

        return $reviewsCollection;
    }
}
