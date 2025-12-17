<?php

declare(strict_types=1);

namespace Twitter\IAM\Domain\User\Model;

use Twitter\IAM\Domain\User\Model\Exception\InvalidEmailException;

final readonly class Email
{
    public function __construct(
        private string $value,
    ) {
    }

    /**
     * @throws InvalidEmailException
     */
    public static function fromString(string $value): self
    {
        $value = trim($value);
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException();
        }

        return new self($value);
    }

    public function toString(): string
    {
        return $this->__toString();
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
