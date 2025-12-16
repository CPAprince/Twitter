<?php

declare(strict_types=1);

namespace Twitter\Shared\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\GuidType;
use Twitter\IAM\Domain\User\Model\UserId;

final class UserIdType extends GuidType
{
    public const string NAME = 'user_id';

    public function getName(): string
    {
        return self::NAME;
    }

    #[\Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (!isset($value)) {
            return null;
        }

        if ($value instanceof UserId) {
            return $value->toString();
        }

        return $value;
    }

    #[\Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?UserId
    {
        if (!isset($value)) {
            return null;
        }

        return UserId::fromString($value);
    }
}
