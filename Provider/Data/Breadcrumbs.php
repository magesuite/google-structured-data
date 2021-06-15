<?php
namespace MageSuite\GoogleStructuredData\Provider\Data;

class Breadcrumbs
{
    public function getBreadcrumbsData($breadcrumbs)
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

        return $breadcrumbData;
    }
}