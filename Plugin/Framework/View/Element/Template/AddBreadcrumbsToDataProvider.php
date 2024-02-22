<?php

namespace MageSuite\GoogleStructuredData\Plugin\Framework\View\Element\Template;

class AddBreadcrumbsToDataProvider
{
    protected \MageSuite\GoogleStructuredData\Helper\Configuration $configuration;
    protected \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer;
    protected \MageSuite\GoogleStructuredData\Provider\Data\Breadcrumbs $breadcrumbsDataProvider;
    protected \Psr\Log\LoggerInterface $logger;

    public function __construct(
        \MageSuite\GoogleStructuredData\Helper\Configuration $configuration,
        \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        \MageSuite\GoogleStructuredData\Provider\Data\Breadcrumbs $breadcrumbsDataProvider,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->configuration = $configuration;
        $this->structuredDataContainer = $structuredDataContainer;
        $this->breadcrumbsDataProvider = $breadcrumbsDataProvider;
        $this->logger = $logger;
    }

    public function aroundAssign(\Magento\Framework\View\Element\Template $subject, callable $proceed, $key = '', $index = null)
    {
        if ($key == 'crumbs') {
            $this->addBreadcrumbsToProvider($index);
        }

        return $proceed($key, $index);
    }

    public function addBreadcrumbsToProvider($breadcrumbs)
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
