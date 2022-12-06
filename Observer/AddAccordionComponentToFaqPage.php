<?php
declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Observer;

class AddAccordionComponentToFaqPage implements \Magento\Framework\Event\ObserverInterface
{
    protected \MageSuite\GoogleStructuredData\Provider\Data\FaqPage\AccordionComponentQuestionList $accordionComponentQuestionList;

    public function __construct(\MageSuite\GoogleStructuredData\Provider\Data\FaqPage\AccordionComponentQuestionList $accordionComponentQuestionList)
    {
        $this->accordionComponentQuestionList = $accordionComponentQuestionList;
    }

    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        $page = $observer->getPage();
        $this->accordionComponentQuestionList->addQuestions($page);
    }
}
