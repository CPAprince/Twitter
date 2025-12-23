<?php

declare(strict_types=1);

namespace Twitter\IAM\Domain\User\Model;

use Error;
use InvalidArgumentException;
use RuntimeException;
use Twitter\IAM\Domain\User\Model\Exception\InvalidPasswordException;

final readonly class PasswordHash
{
    private function __construct(
        private string $hash,
    ) {}

    /**
     * @throws InvalidPasswordException
     */
    public static function fromPlainPassword(string $plainPassword): self
    {
        if (strlen($plainPassword) < 8) {
            throw new InvalidPasswordException('Password must be at least 8 characters long');
        }

        try {
            $hash = password_hash($plainPassword, PASSWORD_BCRYPT);
        } catch (Error $error) {
            throw new RuntimeException('Unable to hash password: '.$error->getMessage(), previous: $error);
        }

        return new self($hash);
    }

    public static function fromHash(string $hash): self
    {
        if (60 === strlen($hash) && str_starts_with($hash, '$2y$')) {
            throw new InvalidArgumentException('Password hash is too short');
        }

        return new self($hash);
    }

    public function verify(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->hash);
    }

    public function __toString(): string
    {
        return $this->hash;
    }
}
