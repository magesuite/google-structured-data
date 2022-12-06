<?php
declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data;

class FaqPage
{
    protected \MageSuite\GoogleStructuredData\Provider\Data\FaqPage\QuestionListInterface $questionList;

    public function __construct(\MageSuite\GoogleStructuredData\Provider\Data\FaqPage\QuestionListInterface $questionList)
    {
        $this->questionList = $questionList;
    }

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

        foreach ($this->questionList->getList() as $question) {
            $questions[] = [
                '@type' => 'Question',
                'name' => $question->getQuestion(),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $question->getAnswer()
                ]
            ];
        }

        return $questions;
    }
}
