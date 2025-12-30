<?php

declare(strict_types=1);

namespace Twitter\Tweet\Domain\Tweet\Model;

use DateTimeImmutable;

final class Tweet
{
    private function __construct(
        private readonly string $id,
        private readonly string $userId,
        private string $content,
        private readonly DateTimeImmutable $createdAt = new DateTimeImmutable(),
        private DateTimeImmutable $updatedAt = new DateTimeImmutable(),
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function content(): string
    {
        return $this->content;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
