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
        // https://regex101.com/r/oZ6r5G/1
        if (!preg_match("/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*\-_.]).{8,}$/m", $plainPassword)) {
            throw new InvalidPasswordException('Password must contain at least one uppercase letter, one lowercase letter, one digit and one special character');
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
        if (60 !== strlen($hash)) {
            throw new InvalidArgumentException('Password hash length does not match');
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
