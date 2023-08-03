<?php
declare(strict_types=1);

namespace MageSuite\GoogleStructuredData\Provider\Data\FaqPage;

class Question
{
    protected string $question;

    protected string $answer;

    public function __construct(string $question, string $answer)
    {
        $this->question = $question;
        $this->answer = $answer;
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function getAnswer(): string
    {
        return $this->answer;
    }
}
