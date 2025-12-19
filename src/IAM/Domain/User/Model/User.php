<?php

declare(strict_types=1);

namespace Twitter\IAM\Domain\User\Model;

use DateTimeImmutable;

final class User
{
    private function __construct(
        private array $roles,
        private string $id,
        private string $email,
        private string $passwordHash,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(Email $email, PasswordHash $passwordHash): self
    {
        return new self(
            ['ROLE_USER'],
            (string) UserId::generate(),
            (string) $email,
            (string) $passwordHash,
            new DateTimeImmutable(),
            new DateTimeImmutable(),
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function roles(): array
    {
        return $this->roles;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function passwordHash(): string
    {
        return $this->passwordHash;
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
