<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data;

class Breadcrumbs
{
    public function __construct(
        protected \Magento\Framework\UrlInterface $url,
        protected \MageSuite\GoogleStructuredData\Helper\Configuration $configuration
    ) {}

    public function getBreadcrumbsData($breadcrumbs): array // phpcs:ignore
    {
        $breadcrumbData = [
            '@type' => 'BreadcrumbList',
            '@id' => $this->url->getCurrentUrl() . '#breadcrumb',
        ];

        if (!is_array($breadcrumbs)) {
            $breadcrumbData['itemListElement'] = [];

            return $breadcrumbData;
        }

        $breadcrumbList = [];
        $i = 1;

        foreach ($breadcrumbs as $breadcrumb) {
            if (isset($breadcrumb['first']) && $breadcrumb['first'] && !$this->configuration->isBreadcrumbHomepageIncluded()) {
                continue;
            }

            if (!$breadcrumb['link']) {
                $breadcrumb['link'] = $this->url->escape($this->url->getCurrentUrl());
            }

            $name = (string) $breadcrumb['label'];
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

        return $breadcrumbData;
    }
}
