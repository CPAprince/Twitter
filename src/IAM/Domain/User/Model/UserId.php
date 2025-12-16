<?php

declare(strict_types=1);

namespace Twitter\IAM\Domain\User\Model;

use Symfony\Component\Uid\Uuid;

final readonly class UserId
{
    public function __construct(
        private string $value,
    ) {
    }

    public static function generate(): self
    {
        return new self(Uuid::v7()->toRfc4122());
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function fromString(string $value): self
    {
        if (!Uuid::isValid($value)) {
            throw new \InvalidArgumentException('Invalid user ID');
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
