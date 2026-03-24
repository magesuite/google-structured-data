<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Plugin\Catalog\Block\Product\ListProduct;

class AddListItemsDataToCategoryPage
{
    public function __construct(
        protected \Magento\Framework\Registry $registry,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
        protected \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        protected \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider,
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Category $categoryConfiguration,
        protected \MageSuite\GoogleStructuredData\Model\ProductStructuredDataIndexRepository $productStructuredDataIndexRepository
    ) {}

    public function afterGetLoadedProductCollection(\Magento\Catalog\Block\Product\ListProduct $subject, $result): \Magento\Eav\Model\Entity\Collection\AbstractCollection
    {
        if (!$this->categoryConfiguration->isCategoryPageIncludeListItem()) {
            return $result;
        }

        $currentCategory = $this->registry->registry('current_category');

        if (!$currentCategory?->getId()) {
            return $result;
        }

        $productIds = $result->getColumnValues('entity_id');
        $store = $this->storeManager->getStore();
        $this->productStructuredDataIndexRepository->loadDataFromIndex($productIds, (int)$store->getId());
        $itemList = [
            "@context" => "https://schema.org/",
            "@type" => "ItemList",
            "itemListElement" => []
        ];
        $position = 1;

        foreach ($result as $product) {
            $listItemData = $this->productDataProvider->getListItemData($product, $store, $position);

            if (!empty($listItemData['item'])) {
                $listItemData['item'] = reset($listItemData['item']);
            }

            $itemList['itemListElement'][] = $listItemData;
            $position++;
        }

        $this->structuredDataContainer->add($itemList, 'item_list');

        return $result;
    }
}
