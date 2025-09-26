<?php

declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data\FaqPage;

class Question
{
    public function __construct(
        protected string $question,
        protected string $answer
    ) {}

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function getAnswer(): string
    {
        return $this->answer;
    }
}
