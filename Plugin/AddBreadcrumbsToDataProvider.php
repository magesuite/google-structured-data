<?php
namespace MageSuite\GoogleStructuredData\Plugin;

class AddBreadcrumbsToDataProvider
{
    /**
     * @var \MageSuite\GoogleStructuredData\Provider\StructuredDataProvider
     */
    private $structuredDataProvider;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \MageSuite\GoogleStructuredData\Provider\StructuredDataProvider $structuredDataProvider,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->structuredDataProvider = $structuredDataProvider;
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
            $breadcrumbData = [
                "@context" => "http://schema.org",
                '@type' => 'BreadcrumbList',
            ];
            $breadcrumbList = [];
            $i = 1;
            foreach ($breadcrumbs as $breadcrumb) {
                if (!$breadcrumb['link']) {
                    continue;
                }
                $name = is_object($breadcrumb['label']) ? $breadcrumb['label']->getText() : $breadcrumb['label'];
                $breadcrumbList[] = [
                    '@type' => 'ListItem',
                    'position' => $i,
                    'item' => [
                        '@id' => $breadcrumb['link'],
                        'name' => $name
                    ]
                ];
                $i++;
            }

            $breadcrumbData['itemListElement'] = $breadcrumbList;

            $structuredData = $this->structuredDataProvider;

            $structuredData->add($breadcrumbData, $structuredData::BREADCRUMBS);

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
