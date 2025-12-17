<?php

declare(strict_types=1);

namespace Twitter\IAM\Domain\User\Model;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    private Uuid $id;
    private string $email;
    private string $password;
    private array $roles;
    private \DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $emailVerifiedAt;

    public function __construct(
        string $email,
        string $passwordHash,
        array $roles = [],
    ) {
        $this->id = Uuid::v7();
        $this->email = $email;
        $this->password = $passwordHash;
        $this->roles = array_unique(['ROLE_USER', ...$roles]);
        $this->createdAt = new \DateTimeImmutable();
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

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    #[\Override]
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return array user roles, guarantee every user at least has `ROLE_USER`
     *
     * @see UserInterface
     */
    #[\Override]
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @return string the public representation of the user (email address)
     *
     * @see UserInterface
     */
    #[\Override]
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getEmailVerifiedAt(): ?\DateTimeImmutable
    {
        return $this->emailVerifiedAt;
    }

    public function markEmailAsVerified(): void
    {
        $this->emailVerifiedAt = new \DateTimeImmutable();
    }

    /**
     * @see UserInterface
     */
    #[\Deprecated]
    #[\Override]
    public function eraseCredentials(): void
    {
    }
}
