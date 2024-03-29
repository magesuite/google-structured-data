<?php

namespace MageSuite\GoogleStructuredData\Test\Integration\Provider\Data;

class FaqPageTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\Cms\Api\PageRepositoryInterface $pageRepository;
    protected ?\MageSuite\GoogleStructuredData\Provider\Data\FaqPage $faqPageDataProvider;
    protected ?\MageSuite\GoogleStructuredData\Provider\Data\FaqPage\AccordionComponentQuestionList $accordionComponentQuestionList;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->pageRepository = $objectManager->get(\Magento\Cms\Api\PageRepositoryInterface::class);
        $this->faqPageDataProvider = $objectManager->get(\MageSuite\GoogleStructuredData\Provider\Data\FaqPage::class);
        $this->accordionComponentQuestionList = $objectManager->get(\MageSuite\GoogleStructuredData\Provider\Data\FaqPage\AccordionComponentQuestionList::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture MageSuite_GoogleStructuredData::Test/Integration/_files/page_with_accordion_component.php
     */
    public function testItReturnFaqPageDataCorrectly(): void
    {
        $page = $this->pageRepository->getById('page-with-accordion-component');
        $this->accordionComponentQuestionList->addQuestions($page);
        $faqPageData = $this->faqPageDataProvider->getFaqPageData();
        $expectedQuestions = [
            [
                '@type' => 'Question',
                'name' => 'Dummy Question',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Dummy Answer'
                ]
            ]
        ];
        $this->assertEquals('https://schema.org', $faqPageData['@context']);
        $this->assertEquals('FAQPage', $faqPageData['@type']);
        $this->assertEquals($expectedQuestions, $faqPageData['mainEntity']);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Cms/_files/pages.php
     */
    public function testItSkipFaqPageDataIfQuestionsDoNotExist(): void
    {
        $page = $this->pageRepository->getById('page100');
        $this->accordionComponentQuestionList->addQuestions($page);
        $faqPageData = $this->faqPageDataProvider->getFaqPageData();
        $this->assertEmpty($faqPageData);
    }
}
