<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data;

class FaqPage
{
    public function __construct(
        protected array $questionLists = []
    ) {}

    public function getFaqPageData(): array
    {
        $questions = $this->getQuestions();

        if (empty($questions)) {
            return [];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $questions
        ];
    }

    protected function getQuestions(): array
    {
        $questions = [];

        foreach ($this->questionLists as $questionList) {
            if (!$questionList instanceof \MageSuite\GoogleStructuredData\Provider\Data\FaqPage\QuestionListInterface) {
                continue;
            }

            foreach ($questionList->getList() as $question) {
                $questions[] = [
                    '@type' => 'Question',
                    'name' => $question->getQuestion(),
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $question->getAnswer()
                    ]
                ];
            }
        }

        return $questions;
    }
}
