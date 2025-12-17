<?php

declare(strict_types=1);

namespace Twitter\IAM\Infrastructure\Persistence\MySQL\Logout;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Twitter\IAM\Application\Logout\RefreshTokenStoreInterface;
use Twitter\IAM\Domain\Auth\Exception\AuthTokenInvalidException;
use Twitter\IAM\Infrastructure\Persistence\Auth\RefreshTokenHasher;
use Twitter\Shared\Infrastructure\Persistence\UuidBinaryConverter;

final class DbalRefreshTokenStore implements RefreshTokenStoreInterface
{
    public function __construct(
        private readonly Connection $connection,
        private readonly RefreshTokenHasher $hasher,
    ) {
    }

    public function revoke(string $refreshToken, string $userId): void
    {
        $now = new \DateTimeImmutable();

        $affected = $this->connection->executeStatement(
            'UPDATE refresh_tokens
     SET revoked_at = :now
     WHERE token_hash = :hash
       AND user_id = :userId
       AND revoked_at IS NULL
       AND expires_at > :now',
            [
                'now' => $now->format('Y-m-d H:i:s'),
                'hash' => $this->hasher->hash($refreshToken),
                'userId' => UuidBinaryConverter::toBytes($userId),
            ],
            [
                'now' => ParameterType::STRING,
                'hash' => ParameterType::BINARY,
                'userId' => ParameterType::BINARY,
            ],
        );

        if (0 === $affected) {
            throw new AuthTokenInvalidException();
        }
    }
}
