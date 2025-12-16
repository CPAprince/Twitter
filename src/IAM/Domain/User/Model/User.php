<?php

declare(strict_types=1);

namespace Twitter\IAM\Domain\User\Model;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

class User
{
    private Uuid $id;
    private string $email;
    private string $password;
    private array $roles;
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $emailVerifiedAt;

    public function __construct(
        string $email,
        string $passwordHash,
        array $roles = ['ROLE_USER']
    ) {
        $this->id = Uuid::v7();
        $this->email = $email;
        $this->password = $passwordHash;
        $this->roles = $roles;
        $this->createdAt = new DateTimeImmutable();
        $this->emailVerifiedAt = null;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getEmailVerifiedAt(): ?DateTimeImmutable
    {
        return $this->emailVerifiedAt;
    }
}
