<?php

declare(strict_types=1);

namespace Twitter\IAM\Domain\User\Model;

use Twitter\IAM\Domain\User\Model\Exception\InvalidPasswordException;

final readonly class PasswordHash
{
    public function __construct(
        private string $hash,
    ) {
    }

    /**
     * @throws InvalidPasswordException
     * @throws \RuntimeException
     */
    public static function fromPlainPassword(string $plainPassword): self
    {
        if (strlen($plainPassword) < 8) {
            throw new InvalidPasswordException('Password must be at least 8 characters long');
        }

        // Use bcrypt to generate 60 characters long hash
        $hash = password_hash($plainPassword, PASSWORD_BCRYPT);

        if (!password_verify($plainPassword, $hash)) {
            throw new \RuntimeException('Unable to hash password');
        }

        return new self($hash);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function fromHash(string $hash): self
    {
        if (strlen($hash) < 60) {
            throw new \InvalidArgumentException('Password hash is too short');
        }

        return new self($hash);
    }

    public function verify(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->hash);
    }

    public function toString(): string
    {
        return $this->__toString();
    }

    public function __toString(): string
    {
        return $this->hash;
    }
}
