<?php

declare(strict_types=1);

namespace Twitter\Shared\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use Twitter\IAM\Domain\User\Model\Email;

final class EmailType extends StringType
{
    public const string NAME = 'email';

    public function getName(): string
    {
        return self::NAME;
    }

    #[\Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (!isset($value)) {
            return null;
        }

        if ($value instanceof Email) {
            return $value->toString();
        }

        return $value;
    }

    #[\Override]
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Email
    {
        if (!isset($value)) {
            return null;
        }

        return Email::fromString($value);
    }
}
