<?php

declare(strict_types=1);

namespace Twitter\Profile\Domain\Profile\Model;

use Assert\Assert;
use DateTimeImmutable;

final class Profile
{
    private function __construct(
        private readonly string $userId,
        private string $name,
        private string $bio,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(string $userId, string $name, ?string $bio = null): self
    {
        Assert::lazy()
            ->tryAll()
            ->that($userId, 'userId')
            ->notBlank()
            ->uuid()
            ->that($name, 'name')
            ->notBlank()
            ->minLength(3)
            ->maxLength(60)
            ->that($bio, 'bio')
            ->nullOr()
            ->maxLength(160)
            ->verifyNow();

        return new self(
            $userId,
            $name,
            $bio ?? '',
            new DateTimeImmutable(),
            new DateTimeImmutable(),
        );
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function bio(): string
    {
        return $this->bio;
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
