<?php

declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$attributeRepository = $objectManager->get(\Magento\Catalog\Api\ProductAttributeRepositoryInterface::class);
$attributeFactory = $objectManager->get(\Magento\Catalog\Api\Data\ProductAttributeInterfaceFactory::class);
$eavSetup = $objectManager->get(\Magento\Eav\Setup\EavSetup::class);
$eavConfig = $objectManager->get(\Magento\Eav\Model\Config::class);

$attributeSetId = $eavSetup->getAttributeSetId(\Magento\Catalog\Model\Product::ENTITY, 'Default');
$groupId = $eavSetup->getDefaultAttributeGroupId(\Magento\Catalog\Model\Product::ENTITY, $attributeSetId);

$attributeModel = $attributeFactory->create();
$attributeModel->setData([
    'attribute_code' => 'test_age_attr',
    'entity_type_id' => $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY),
    'frontend_input' => 'select',
    'frontend_label' => ['Test Age Attribute'],
    'backend_type' => 'int',
    'is_global' => 1,
    'is_user_defined' => 1,
    'is_required' => 0,
    'is_searchable' => 0,
    'is_filterable' => 0,
    'is_visible_on_front' => 0,
    'used_in_product_listing' => 0,
    'option' => [
        'value' => ['option_0' => ['3-6'], 'option_1' => ['1½+']],
        'order' => ['option_0' => 1, 'option_1' => 2],
    ],
]);

$attribute = $attributeRepository->save($attributeModel);
$eavSetup->addAttributeToGroup(\Magento\Catalog\Model\Product::ENTITY, $attributeSetId, $groupId, $attribute->getId());
$eavConfig->clear();
