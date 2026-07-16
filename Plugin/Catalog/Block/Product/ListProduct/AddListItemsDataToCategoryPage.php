<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Plugin\Catalog\Block\Product\ListProduct;

class AddListItemsDataToCategoryPage
{
    public function __construct(
        protected \Magento\Framework\Registry $registry,
        protected \Magento\Framework\UrlInterface $url,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
        protected \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        protected \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider,
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Category $categoryConfiguration,
        protected \MageSuite\GoogleStructuredData\Model\Product\StructuredDataPreloader $preloader
    ) {}

    public function afterGetLoadedProductCollection(\Magento\Catalog\Block\Product\ListProduct $subject, $result): \Magento\Eav\Model\Entity\Collection\AbstractCollection
    {
        if (!$this->categoryConfiguration->isListItemEnabled()) {
            return $result;
        }

        $currentCategory = $this->registry->registry('current_category');

        if (!$currentCategory?->getId()) {
            return $result;
        }

        $store = $this->storeManager->getStore();

        if ($this->categoryConfiguration->shouldEmbedFullProductData()) {
            $this->preloader->preload($result, (int)$store->getId());
        }

        $listElements = [];
        $position = 1;

        foreach ($result as $product) {
            $listItemData = $this->productDataProvider->getListItemData($product, $store, $position);

            if (!empty($listItemData['item'])) {
                $listItemData['item'] = reset($listItemData['item']);
            }

            $listElements[] = $listItemData;
            $position++;
        }

        $itemList = [
            "@type" => "ItemList",
            "@id" => $this->url->getCurrentUrl() . '#itemlist',
            "name" => $currentCategory->getName(),
            "numberOfItems" => count($listElements),
            "itemListOrder" => "https://schema.org/ItemListOrderAscending",
            "itemListElement" => $listElements
        ];

        $this->structuredDataContainer->add($itemList, 'item_list');

        return $result;
    }
}
