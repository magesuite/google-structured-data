<?php
namespace MageSuite\GoogleStructuredData\Plugin;

class AddBreadcrumbsToDataProvider
{
    /**
     * @var \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer
     */
    protected $structuredDataContainer;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var \MageSuite\GoogleStructuredData\Provider\Data\Breadcrumbs
     */
    protected $breadcrumbsDataProvider;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \MageSuite\GoogleStructuredData\Provider\StructuredDataContainer $structuredDataContainer,
        \Psr\Log\LoggerInterface $logger,
        \MageSuite\GoogleStructuredData\Provider\Data\Breadcrumbs $breadcrumbsDataProvider,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->structuredDataContainer = $structuredDataContainer;
        $this->logger = $logger;
        $this->breadcrumbsDataProvider = $breadcrumbsDataProvider;
        $this->scopeConfig = $scopeConfig;
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
            if(!$this->scopeConfig->getValue('structured_data/breadcrumbs/enabled')){
                return;
            }

            $breadcrumbData = $this->breadcrumbsDataProvider->getBreadcrumbsData($breadcrumbs);

            $this->structuredDataContainer->add($breadcrumbData, 'breadcrumbs');
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
