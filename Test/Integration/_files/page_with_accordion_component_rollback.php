<?php
declare(strict_types=1);
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$pageRepository = $objectManager->create(\Magento\Cms\Api\PageRepositoryInterface::class);
$searchCriteriaBuilder = $objectManager->get(\Magento\Framework\Api\SearchCriteriaBuilder::class);
$searchCriteria = $searchCriteriaBuilder->addFilter(\Magento\Cms\Api\Data\PageInterface::IDENTIFIER, 'page-with-accordion-component')
    ->create();
$result = $pageRepository->getList($searchCriteria);

foreach ($result->getItems() as $item) {
    $pageRepository->delete($item);
}
