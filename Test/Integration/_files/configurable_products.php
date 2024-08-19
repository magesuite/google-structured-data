<?php


$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(\Magento\Store\Api\WebsiteRepositoryInterface::class);
$baseWebsite = $websiteRepository->get('base');

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
/** @var \Magento\Catalog\Api\Data\ProductInterfaceFactory $productInterfaceFactory */
$productInterfaceFactory = $objectManager->get(\Magento\Catalog\Api\Data\ProductInterfaceFactory::class);

/** @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository */
$attributeRepository = $objectManager->get(\Magento\Catalog\Api\ProductAttributeRepositoryInterface::class);
/** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
$attribute = $attributeRepository->get('test_configurable');
/** @var \Magento\Eav\Api\Data\AttributeOptionInterface[] $options */
$options = $attribute->getOptions();

/** @var $installer \Magento\Eav\Setup\EavSetup */
$installer = $objectManager->get(\Magento\Eav\Setup\EavSetup::class);
$attributeSetId = $installer->getAttributeSetId(\Magento\Catalog\Model\Product::ENTITY, 'Default');

/** @var \Magento\ConfigurableProduct\Helper\Product\Options\Factory $optionsFactory */
$optionsFactory = $objectManager->get(\Magento\ConfigurableProduct\Helper\Product\Options\Factory::class);
/* Create simple products per each option value*/

$attributeValues = [];
$associatedProductIds = [];
$productIds = [10, 20];
array_shift($options); //remove the first option which is empty

foreach ($options as $option) {
    /** @var $product \Magento\Catalog\Model\Product */
    $product = $productInterfaceFactory->create();
    $productId = array_shift($productIds);
    $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
        ->setAttributeSetId($attributeSetId)
        ->setWebsiteIds([$baseWebsite->getId()])
        ->setName('Configurable Option' . $option->getLabel())
        ->setSku('simple_' . $productId)
        ->setPrice($productId)
        ->setTestConfigurable($option->getValue())
        ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE)
        ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
        ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);
    $simple1 = $productRepository->save($product);

    $attributeValues[] = [
        'label' => 'test',
        'attribute_id' => $attribute->getId(),
        'value_index' => $option->getValue(),
    ];
    $associatedProductIds[] = $simple1->getId();
}

/** @var $product \Magento\Catalog\Model\Product */
$product = $productInterfaceFactory->create();
$configurableAttributesData = [
    [
        'attribute_id' => $attribute->getId(),
        'code' => $attribute->getAttributeCode(),
        'label' => $attribute->getStoreLabel(),
        'position' => '0',
        'values' => $attributeValues,
    ],
];
$configurableOptions = $optionsFactory->create($configurableAttributesData);
$extensionConfigurableAttributes = $product->getExtensionAttributes();
$extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);
$extensionConfigurableAttributes->setConfigurableProductLinks($associatedProductIds);
$product->setExtensionAttributes($extensionConfigurableAttributes);

$product->setTypeId(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
    ->setAttributeSetId($attributeSetId)
    ->setWebsiteIds([$baseWebsite->getId()])
    ->setName('Configurable Product')
    ->setSku('configurable')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1]);
$productRepository->cleanCache();
$productRepository->save($product);

/* Create simple products per each option value*/
/** @var \Magento\Eav\Api\Data\AttributeOptionInterface[] $options */
$options = $attribute->getOptions();

$attributeValues = [];
$associatedProductIds = [];
$productIds = [30, 40];
array_shift($options); //remove the first option which is empty

foreach ($options as $option) {
    /** @var $product \Magento\Catalog\Model\Product */
    $product = $productInterfaceFactory->create();
    $productId = array_shift($productIds);
    $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
        ->setAttributeSetId($attributeSetId)
        ->setWebsiteIds([$baseWebsite->getId()])
        ->setName('Configurable Option' . $option->getLabel())
        ->setSku('simple_' . $productId)
        ->setPrice($productId)
        ->setTestConfigurable($option->getValue())
        ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE)
        ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
        ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);
    $simple2 = $productRepository->save($product);

    $attributeValues[] = [
        'label' => 'test',
        'attribute_id' => $attribute->getId(),
        'value_index' => $option->getValue(),
    ];
    $associatedProductIds[] = $simple2->getId();
}

/** @var $product \Magento\Catalog\Model\Product */
$product = $productInterfaceFactory->create();

$configurableAttributesData = [
    [
        'attribute_id' => $attribute->getId(),
        'code' => $attribute->getAttributeCode(),
        'label' => $attribute->getStoreLabel(),
        'position' => '0',
        'values' => $attributeValues,
    ],
];

$configurableOptions = $optionsFactory->create($configurableAttributesData);

$extensionConfigurableAttributes = $product->getExtensionAttributes();
$extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);
$extensionConfigurableAttributes->setConfigurableProductLinks($associatedProductIds);

$product->setExtensionAttributes($extensionConfigurableAttributes);

$product->setTypeId(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
    ->setAttributeSetId($attributeSetId)
    ->setWebsiteIds([$baseWebsite->getId()])
    ->setName('Configurable Product 12345')
    ->setSku('configurable_12345')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1]);
$productRepository->cleanCache();
$productRepository->save($product);
