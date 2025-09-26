<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Plugin\Framework\View\Element\Template;

class AddBreadcrumbsToDataProvider
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Helper\Configuration $configuration,
        protected \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        protected \MageSuite\GoogleStructuredData\Provider\Data\Breadcrumbs $breadcrumbsDataProvider,
        protected \Psr\Log\LoggerInterface $logger
    ) {}

    public function aroundAssign(\Magento\Framework\View\Element\Template $subject, callable $proceed, $key = '', $index = null) // phpcs:ignore
    {
        if ($key === 'crumbs') {
            $this->addBreadcrumbsToProvider($index);
        }

        return $proceed($key, $index);
    }

    public function addBreadcrumbsToProvider($breadcrumbs): void // phpcs:ignore
    {
        try {
            if (!$this->configuration->isBreadcrumbsEnabled()) {
                return;
            }

            if (empty($breadcrumbs)) {
                return;
            }

            $breadcrumbData = $this->breadcrumbsDataProvider->getBreadcrumbsData($breadcrumbs);

            $this->structuredDataContainer->add($breadcrumbData, 'breadcrumbs');
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
