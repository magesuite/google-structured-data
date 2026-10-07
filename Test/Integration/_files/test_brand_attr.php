<?php

declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$eavSetup = $objectManager->get(\Magento\Eav\Setup\EavSetup::class);
$eavConfig = $objectManager->get(\Magento\Eav\Model\Config::class);

$eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'test_brand_attr', [
    'type' => 'varchar',
    'input' => 'multiselect',
    'label' => 'Test Brand Attribute',
    'backend' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
    'source' => \MageSuite\GoogleStructuredData\Test\Integration\Stub\BrandSourceModel::class,
    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
    'user_defined' => true,
    'required' => false,
    'attribute_set' => 'Default',
    'group' => 'General',
]);

$eavConfig->clear();
