<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Model\Product;

class DescriptionFilter
{
    public function __construct(
        protected \Magento\Framework\Filter\StripTags $stripTags,
        protected \MageSuite\GoogleStructuredData\Helper\Configuration\Product $productConfiguration
    ) {}

    public function filter(string $description, ?int $storeId = null): string
    {
        $plainDescription = $this->normalizeWhitespace($this->stripTags->filter($description));
        $maxLength = $this->productConfiguration->getDescriptionMaxLength($storeId);

        if ($maxLength <= 0 || mb_strlen($plainDescription) <= $maxLength) {
            return $plainDescription;
        }

        return rtrim(mb_substr($plainDescription, 0, $maxLength));
    }

    protected function normalizeWhitespace(string $text): string
    {
        return trim((string)preg_replace('/\s+/u', ' ', $text));
    }
}
