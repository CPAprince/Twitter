<?php

declare(strict_types=1);

namespace Twitter\IAM\Domain\User\Model;

final class User
{
    private function __construct(
        private UserId $id,
        private Email $email,
        private PasswordHash $passwordHash,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {
    }

    public static function create(Email $email, PasswordHash $passwordHash): self
    {
        return new self(UserId::generate(), $email, $passwordHash, new \DateTimeImmutable(), new \DateTimeImmutable());
    }

    public function getId(): UserId
    {
        return $this->id;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPasswordHash(): PasswordHash
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
