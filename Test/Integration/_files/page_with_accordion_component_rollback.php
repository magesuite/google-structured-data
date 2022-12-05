<?php
declare(strict_types=1);
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$pageRepository = $objectManager->create(\Magento\Cms\Api\PageRepositoryInterface::class);
$pageRepository->deleteById('page-with-accordion-component');
