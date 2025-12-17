<?php

declare(strict_types=1);

namespace Twitter\Tests\IAM\Infrastructure\Persistence\MySQL\Logout;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use PHPUnit\Framework\TestCase;
use Twitter\IAM\Domain\Auth\Exception\AuthTokenInvalidException;
use Twitter\IAM\Infrastructure\Persistence\Auth\RefreshTokenHasher;
use Twitter\IAM\Infrastructure\Persistence\MySQL\Logout\DbalRefreshTokenStore;
use Twitter\Shared\Infrastructure\Persistence\UuidBinaryConverter;

final class DbalRefreshTokenStoreTest extends TestCase
{
    public function testRevokeSuccessUpdatesOneRow(): void
    {
        $connection = $this->createMock(Connection::class);

        $userId = '019b2bd9-f57c-7088-824e-b6f96f27a1ba';
        $refreshToken = 'rt_example_123';
        $secret = 'test_secret';

        $hasher = new RefreshTokenHasher($secret);

        $expectedHash = hash_hmac('sha256', $refreshToken, $secret, true); // 32 bytes
        $expectedUserBytes = UuidBinaryConverter::toBytes($userId); // 16 bytes

        $connection->expects(self::once())
            ->method('executeStatement')
            ->with(
                self::callback(function (string $sql): bool {
                    return str_contains($sql, 'UPDATE refresh_tokens')
                        && str_contains($sql, 'SET revoked_at = :now')
                        && str_contains($sql, 'WHERE token_hash = :hash')
                        && str_contains($sql, 'AND user_id = :userId')
                        && str_contains($sql, 'AND revoked_at IS NULL')
                        && str_contains($sql, 'AND expires_at > :now');
                }),
                self::callback(function (array $params) use ($expectedHash, $expectedUserBytes): bool {
                    if (!isset($params['now'], $params['hash'], $params['userId'])) {
                        return false;
                    }

                    if (!is_string($params['now']) || '' === $params['now']) {
                        return false;
                    }

                    if (!is_string($params['hash']) || 32 !== strlen($params['hash'])) {
                        return false;
                    }
                    if (!hash_equals($expectedHash, $params['hash'])) {
                        return false;
                    }

                    if (!is_string($params['userId']) || 16 !== strlen($params['userId'])) {
                        return false;
                    }
                    if (!hash_equals($expectedUserBytes, $params['userId'])) {
                        return false;
                    }

                    return true;
                }),
                self::callback(function (array $types): bool {
                    return ($types['hash'] ?? null) === ParameterType::BINARY
                        && ($types['userId'] ?? null) === ParameterType::BINARY;
                })
            )
            ->willReturn(1);

        $store = new DbalRefreshTokenStore($connection, $hasher);

        $store->revoke($refreshToken, $userId);
    }

    public function testRevokeZeroAffectedRowsThrowsAuthTokenInvalid(): void
    {
        $connection = $this->createMock(Connection::class);

        $userId = '019b2bd9-f57c-7088-824e-b6f96f27a1ba';
        $refreshToken = 'rt_example_123';
        $secret = 'test_secret';

        $hasher = new RefreshTokenHasher($secret);

        $connection->expects(self::once())
            ->method('executeStatement')
            ->willReturn(0);

        $store = new DbalRefreshTokenStore($connection, $hasher);

        $this->expectException(AuthTokenInvalidException::class);

        $store->revoke($refreshToken, $userId);
    }
}
