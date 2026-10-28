<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Test\Integration\Model\Product;

class DescriptionFilterTest extends \PHPUnit\Framework\TestCase
{
    protected ?\MageSuite\GoogleStructuredData\Model\Product\DescriptionFilter $descriptionFilter;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->descriptionFilter = $objectManager->create(\MageSuite\GoogleStructuredData\Model\Product\DescriptionFilter::class);
    }

    public function testItLimitsDescriptionToFiveThousandCharactersByDefault(): void
    {
        $description = str_repeat('a', 5001);

        $this->assertSame(str_repeat('a', 5000), $this->descriptionFilter->filter($description));
    }

    #[\Magento\TestFramework\Fixture\Config('structured_data/product_page/description_max_length', '6', 'store', 'default')]
    public function testItCountsMultibyteCharactersAfterStrippingTags(): void
    {
        $this->assertSame('Zażółć', $this->descriptionFilter->filter('<p>Zażółć gęślą jaźń</p>'));
    }

    public function testItReplacesLineBreaksAndTabsWithSingleSpace(): void
    {
        $description = "<p>First line</p>\r\n<p>Second\tline</p>\n\n  Third line  ";

        $this->assertSame('First line Second line Third line', $this->descriptionFilter->filter($description));
    }

    #[\Magento\TestFramework\Fixture\Config('structured_data/product_page/description_max_length', '5', 'store', 'default')]
    public function testItCountsLimitAfterNormalizingWhitespace(): void
    {
        $this->assertSame('ab cd', $this->descriptionFilter->filter("ab\r\n\r\ncd ef"));
    }

    #[\Magento\TestFramework\Fixture\Config('structured_data/product_page/description_max_length', '0', 'store', 'default')]
    public function testItDoesNotLimitDescriptionWhenMaxLengthIsZero(): void
    {
        $description = str_repeat('a', 5001);

        $this->assertSame($description, $this->descriptionFilter->filter($description));
    }
}
