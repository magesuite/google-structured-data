<?php

declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$attributeRepository = $objectManager->get(\Magento\Catalog\Api\ProductAttributeRepositoryInterface::class);

try {
    $attributeRepository->deleteById('test_brand_attr');
} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
}
