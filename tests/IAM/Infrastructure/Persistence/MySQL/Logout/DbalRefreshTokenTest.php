<?php

declare(strict_types=1);

namespace Twitter\Tests\IAM\Infrastructure\Persistence\MySQL\Logout;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twitter\IAM\Domain\Auth\Exception\TokenInvalidException;
use Twitter\IAM\Infrastructure\Auth\RefreshTokenHasher;
use Twitter\IAM\Infrastructure\Persistence\MySQL\Logout\MySQLRefreshToken;

#[Group('unit')]
#[CoversClass(MySQLRefreshToken::class)]
final class DbalRefreshTokenTest extends TestCase
{
    private Connection&MockObject $connection;
    private MySQLRefreshToken $store;

    private const string USER_ID = '019b2bd9-f57c-7088-824e-b6f96f27a1ba';
    private const string REFRESH_TOKEN = 'rt_example_123';
    private const string SECRET = 'test_secret';

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);

        $hasher = new RefreshTokenHasher(self::SECRET);
        $this->store = new MySQLRefreshToken($this->connection, $hasher);
    }

    #[Test]
    public function itRevokesTokenWhenOneRowWasUpdated(): void
    {
        $this->connection
            ->expects(self::once())
            ->method('executeStatement')
            ->willReturn(1);

        $this->store->revoke(self::REFRESH_TOKEN, self::USER_ID);
    }

    #[Test]
    public function itThrowsWhenNoRowsWereUpdated(): void
    {
        $this->connection
            ->expects(self::once())
            ->method('executeStatement')
            ->willReturn(0);

        $this->expectException(TokenInvalidException::class);

        $this->store->revoke(self::REFRESH_TOKEN, self::USER_ID);
    }
}
