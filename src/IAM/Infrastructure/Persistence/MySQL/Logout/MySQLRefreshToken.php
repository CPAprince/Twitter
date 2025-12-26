<?php

declare(strict_types=1);

namespace Twitter\IAM\Infrastructure\Persistence\MySQL\Logout;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Twitter\IAM\Application\Logout\RefreshTokenRepository;
use Twitter\IAM\Domain\Auth\Exception\TokenInvalidException;
use Twitter\IAM\Infrastructure\Auth\RefreshTokenHasher;
use Twitter\Shared\Infrastructure\Persistence\Doctrine\UuidBinaryConverter;

final class MySQLRefreshToken implements RefreshTokenRepository
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
            <<<SQL
                UPDATE refresh_tokens
                SET revoked_at = :now
                WHERE token_hash = :hash
                  AND user_id = :userId
                  AND revoked_at IS NULL
                  AND expires_at > :now
                SQL,
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
            throw new TokenInvalidException();
        }
    }

    public function deleteRevokedExpired(\DateTimeImmutable $now): int
    {
        return $this->connection->executeStatement(
            <<<SQL
                DELETE FROM refresh_tokens
                WHERE revoked_at IS NOT NULL
                  AND expires_at <= :now
                SQL,
            ['now' => $now->format('Y-m-d H:i:s')],
            ['now' => ParameterType::STRING],
        );
    }
}
