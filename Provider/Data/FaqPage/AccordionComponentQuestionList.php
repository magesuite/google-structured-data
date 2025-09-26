<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data\FaqPage;

class AccordionComponentQuestionList implements QuestionListInterface
{
    protected array $questionList = [];

    public function __construct(
        protected \Magento\Framework\Serialize\SerializerInterface $serializer,
        protected \MageSuite\GoogleStructuredData\Provider\Data\FaqPage\QuestionFactory $questionFactory
    ) {}

    /**
     * @return \MageSuite\GoogleStructuredData\Provider\Data\FaqPage\Question[]
     */
    public function getList(): array
    {
        return $this->questionList;
    }

    public function addQuestions(?string $contentConstructorContent): void
    {
        if (empty($contentConstructorContent)) {
            return;
        }

        try {
            $components = $this->serializer->unserialize($contentConstructorContent);
        } catch (\InvalidArgumentException $e) {
            return;
        }

        foreach ($components as $component) {
            if (!isset($component['type']) || $component['type'] !== 'accordion') {
                continue;
            }

            foreach ($component['data']['groups'] as $group) {
                foreach ($group['items'] as $question) {
                    $this->questionList[] = $this->questionFactory->create(
                        [
                            'question' => $question['headline'],
                            'answer' => strip_tags($question['content'])
                        ]
                    );
                }
            }
        }
    }
}
