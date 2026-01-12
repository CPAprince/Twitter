<?php

declare(strict_types=1);

namespace Twitter\Like\Domain\Like\Model;

final class Like
{
    private function __construct(
        private readonly string $tweetId,
        private readonly string $userId,
    ) {}

    public static function create(
        string $tweetId,
        string $userId,
    ): self {
        return new self($tweetId, $userId);
    }

    public function tweetId(): string
    {
        return $this->tweetId;
    }

    public function userId(): string
    {
        return $this->userId;
    }
}
