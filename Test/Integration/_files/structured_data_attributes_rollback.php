<?php

declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$attributeRepository = $objectManager->get(\Magento\Catalog\Api\ProductAttributeRepositoryInterface::class);

foreach (['sd_flagged_select', 'sd_flagged_text', 'sd_not_flagged'] as $attributeCode) {
    try {
        $attributeRepository->deleteById($attributeCode);
    } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
    }
}

\Magento\TestFramework\Workaround\Override\Fixture\Resolver::getInstance()->requireDataFixture('MageSuite_GoogleStructuredData::Test/Integration/_files/products_simple_rollback.php');
