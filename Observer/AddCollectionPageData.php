<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Observer;

class AddCollectionPageData implements \Magento\Framework\Event\ObserverInterface
{
    protected const CATEGORY_VIEW_ACTION_NAME = 'catalog_category_view';

    public function __construct(
        protected \Magento\Framework\Registry $registry,
        protected \Magento\Framework\UrlInterface $url,
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Category $categoryConfiguration,
        protected \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        protected \MageSuite\GoogleStructuredData\Provider\Data\CollectionPage $collectionPageDataProvider
    ) {}

    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        if (!$this->categoryConfiguration->isCollectionPageEnabled()) {
            return;
        }

        if (!$this->isCategoryRequest($observer)) {
            return;
        }

        $currentCategory = $this->registry->registry('current_category');

        if (!$currentCategory?->getId()) {
            return;
        }

        $collectionPageData = $this->collectionPageDataProvider->getCollectionPageData($currentCategory, $this->getCurrentPageUrl());

        $this->structuredDataContainer->add($collectionPageData, 'collection_page');
    }

    protected function isCategoryRequest(\Magento\Framework\Event\Observer $observer): bool
    {
        $request = $observer->getData('request');

        return $request?->getFullActionName() === self::CATEGORY_VIEW_ACTION_NAME;
    }

    protected function getCurrentPageUrl(): string
    {
        return $this->url->getUrl('*/*/*', [
            '_current' => true,
            '_use_rewrite' => true
        ]);
    }
}
