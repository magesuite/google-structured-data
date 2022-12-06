<?php
declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data\FaqPage;

interface QuestionListInterface
{
    /**
     * @return \MageSuite\GoogleStructuredData\Provider\Data\FaqPage\Question[]
     */
    public function getList(): array;
}
