<?php

declare(strict_types=1);

\Magento\TestFramework\Workaround\Override\Fixture\Resolver::getInstance()->requireDataFixture('MageSuite_GoogleStructuredData::Test/Integration/_files/products_simple.php');

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$attributeRepository = $objectManager->get(\Magento\Catalog\Api\ProductAttributeRepositoryInterface::class);
$attributeFactory = $objectManager->get(\Magento\Catalog\Api\Data\ProductAttributeInterfaceFactory::class);
$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$eavSetup = $objectManager->get(\Magento\Eav\Setup\EavSetup::class);
$eavConfig = $objectManager->get(\Magento\Eav\Model\Config::class);

$attributeSetId = $eavSetup->getAttributeSetId(\Magento\Catalog\Model\Product::ENTITY, 'Default');
$groupId = $eavSetup->getDefaultAttributeGroupId(\Magento\Catalog\Model\Product::ENTITY, $attributeSetId);
$commonAttributeData = [
    'entity_type_id' => $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY),
    'is_global' => 1,
    'is_user_defined' => 1
];
$attributesData = [
    [
        'attribute_code' => 'sd_flagged_select',
        'frontend_input' => 'select',
        'frontend_label' => ['Flagged Select'],
        'backend_type' => 'int',
        'use_in_structured_data' => 1,
        'structured_data_description' => '{"default": "Context of the default store view", "second": "Context of another store view"}',
        'option' => [
            'value' => [
                'option_0' => ['Small'],
                'option_1' => ['Large']
            ],
            'order' => [
                'option_0' => 1,
                'option_1' => 2
            ]
        ]
    ],
    [
        'attribute_code' => 'sd_flagged_text',
        'frontend_input' => 'textarea',
        'frontend_label' => ['Flagged Text'],
        'backend_type' => 'text',
        'use_in_structured_data' => 1,
        'structured_data_description' => 'Plain context'
    ],
    [
        'attribute_code' => 'sd_not_flagged',
        'frontend_input' => 'text',
        'frontend_label' => ['Not Flagged'],
        'backend_type' => 'varchar',
        'use_in_structured_data' => 0
    ]
];

foreach ($attributesData as $attributeData) {
    $attributeModel = $attributeFactory->create();
    $attributeModel->setData($attributeData + $commonAttributeData);
    $attribute = $attributeRepository->save($attributeModel);
    $eavSetup->addAttributeToGroup(\Magento\Catalog\Model\Product::ENTITY, $attributeSetId, $groupId, $attribute->getId());
}

$eavConfig->clear();

$product = $productRepository->get('simple', true, \Magento\Store\Model\Store::DEFAULT_STORE_ID, true);
$product->setData('sd_flagged_select', $eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'sd_flagged_select')->getSource()->getOptionId('Large'));
$product->setData('sd_flagged_text', '<p>Text&nbsp;value</p>');
$product->setData('sd_not_flagged', 'Hidden value');
$productRepository->save($product);
