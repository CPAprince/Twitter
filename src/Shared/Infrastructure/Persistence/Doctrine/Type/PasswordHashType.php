<?php

declare(strict_types=1);

namespace Twitter\Shared\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use Twitter\IAM\Domain\User\Model\PasswordHash;

final class PasswordHashType extends StringType
{
    public const string NAME = 'password_hash';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (!isset($value)) {
            return null;
        }

        if ($value instanceof PasswordHash) {
            return $value->toString();
        }

        return $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?PasswordHash
    {
        if (!isset($value)) {
            return null;
        }

        return PasswordHash::fromHash($value);
    }
}
