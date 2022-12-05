<?php
declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data;

class FaqPage
{
    protected \Magento\Framework\Serialize\SerializerInterface $serializer;

    public function __construct(\Magento\Framework\Serialize\SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function getFaqPageData(\Magento\Cms\Api\Data\PageInterface $page): array
    {
        $questions = $this->getQuestions($page);

        if (empty($questions)) {
            return [];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $questions
        ];
    }

    public function getQuestions(\Magento\Cms\Api\Data\PageInterface $page): array
    {
        $contentConstructorContent = $page->getContentConstructorContent();

        try {
            $components = $this->serializer->unserialize($contentConstructorContent);
        } catch (\InvalidArgumentException $e) {
            return [];
        }

        $questions = [];

        foreach ($components as $component) {
            if (!isset($component['type']) || $component['type'] !== 'accordion') {
                continue;
            }

            foreach ($component['data']['groups'] as $group) {
                foreach ($group['items'] as $question) {
                    $questions[] = [
                        '@type' => 'Question',
                        'name' => $question['headline'],
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => strip_tags($question['content'])
                        ]
                    ];
                }
            }
        }

        return $questions;
    }
}
