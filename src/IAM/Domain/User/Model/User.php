<?php

declare(strict_types=1);

namespace Twitter\IAM\Domain\User\Model;

final class User
{
    private function __construct(
        private array $roles,
        private string $id,
        private string $email,
        private string $passwordHash,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {}

    public static function create(Email $email, PasswordHash $passwordHash): self
    {
        return new self(
            ['ROLE_USER'],
            UserId::generate()->toString(),
            $email->toString(),
            $passwordHash->toString(),
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
