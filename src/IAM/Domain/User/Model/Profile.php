<?php

declare(strict_types=1);

namespace Twitter\IAM\Domain\User\Model;

final class Profile
{
    private User $user;
    private string $name;
    private ?string $bio;
    private ?\DateTimeImmutable $updatedAt;

    public function __construct(User $user, string $name, ?string $bio = null)
    {
        $this->user = $user;
        $this->name = $name;
        $this->bio = $bio;
        $this->updatedAt = null;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function changeName(string $newName): void
    {
        if ($this->name !== $newName) {
            $this->name = $newName;
            $this->touch();
        }
    }

    public function changeBio(?string $newBio): void
    {
        if ($this->bio !== $newBio) {
            $this->bio = $newBio;
            $this->touch();
        }
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
