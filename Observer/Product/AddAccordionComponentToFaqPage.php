<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Observer\Product;

class AddAccordionComponentToFaqPage implements \Magento\Framework\Event\ObserverInterface
{
    protected \MageSuite\GoogleStructuredData\Provider\Data\FaqPage\AccordionComponentQuestionList $accordionComponentQuestionList;
    protected \MageSuite\GoogleStructuredData\Helper\Configuration\FaqPage $configuration;

    public function __construct(
        \MageSuite\GoogleStructuredData\Provider\Data\FaqPage\AccordionComponentQuestionList $accordionComponentQuestionList,
        \MageSuite\GoogleStructuredData\Helper\Configuration\FaqPage $configuration
    ) {
        $this->accordionComponentQuestionList = $accordionComponentQuestionList;
        $this->configuration = $configuration;
    }

    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        if (!$this->configuration->isEnabledOnProduct()) {
            return;
        }

        $product = $observer->getProduct();

        if (!$product) {
            return;
        }

        $contentConstructorContent = $product->getContentConstructorContent();
        $this->accordionComponentQuestionList->addQuestions($contentConstructorContent);
    }
}
