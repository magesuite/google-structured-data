<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Cron;

class DailyCatalogUpdate
{
    public function __construct(
        protected \MageSuite\GoogleStructuredData\Model\Indexer\Product\Processor $processor
    ) {}

    public function execute(): void
    {
        $this->processor->markIndexerAsInvalid();
    }
}
