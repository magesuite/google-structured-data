<?php

namespace MageSuite\GoogleStructuredData\Plugin\Catalog\Block\Product\ListProduct;

class AddListItemsDataToCategoryPage
{
    protected \Magento\Framework\Registry $registry;
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;
    protected \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer;
    protected \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider;
    protected \MageSuite\GoogleStructuredData\Helper\Configuration\Category $categoryConfiguration;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        \MageSuite\GoogleStructuredData\Provider\Data\Product $productDataProvider,
        \MageSuite\GoogleStructuredData\Helper\Configuration\Category $categoryConfiguration
    ) {
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->structuredDataContainer = $structuredDataContainer;
        $this->productDataProvider = $productDataProvider;
        $this->categoryConfiguration = $categoryConfiguration;
    }

    public function afterGetLoadedProductCollection(\Magento\Catalog\Block\Product\ListProduct $subject, $result): \Magento\Eav\Model\Entity\Collection\AbstractCollection
    {
        if (!$this->categoryConfiguration->isCategoryPageIncludeListItem()) {
            return $result;
        }

        $currentCategory = $this->registry->registry('current_category');
        if (!isset($currentCategory) || !$currentCategory->getId()) {
            return $result;
        }

        $store = $this->storeManager->getStore();
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
