<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data;

class Breadcrumbs
{
    public function __construct(
        protected \Magento\Framework\UrlInterface $url
    ) {}

    public function getBreadcrumbsData($breadcrumbs): array // phpcs:ignore
    {
        $breadcrumbData = [
            "@context" => "http://schema.org",
            '@type' => 'BreadcrumbList',
        ];

        if (!is_array($breadcrumbs)) {
            $breadcrumbData['itemListElement'] = [];

            return $breadcrumbData;
        }

        $breadcrumbList = [];
        $i = 1;

        foreach ($breadcrumbs as $breadcrumb) {
            if (isset($breadcrumb['first']) && $breadcrumb['first']) {
                continue;
            }

            if (!$breadcrumb['link']) {
                $breadcrumb['link'] = $this->url->escape($this->url->getCurrentUrl());
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

        return $breadcrumbData;
    }
}
