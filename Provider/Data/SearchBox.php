<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data;

class SearchBox
{
    public function __construct(
        protected \Magento\Framework\UrlInterface $urlBuilder
    ) {}

    public function getSearchBoxData(): array
    {
        $searchUrl = rtrim($this->urlBuilder->getUrl('catalogsearch/result/?q={search_term_string}'), '/');

        return [
            "@type" => "SearchAction",
            "target" => [
                '@type' => 'EntryPoint',
                'urlTemplate' => $searchUrl
            ],
            "query-input" => "required name=search_term_string"
        ];
    }
}
