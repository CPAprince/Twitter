<?php

declare(strict_types=1);

namespace Twitter\Tweet\UI\REST\CreateTweet;

final readonly class CreateTweetRequest
{
    public function __construct(
        private string $userId,
        private string $content,
    ) {}

    public function userId(): string
    {
        return $this->userId;
    }

    public function content(): string
    {
        return $this->content;
    }
}
