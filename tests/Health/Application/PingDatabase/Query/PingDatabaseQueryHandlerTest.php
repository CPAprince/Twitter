<?php

declare(strict_types=1);

namespace Twitter\Tests\Health\Application\PingDatabase\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twitter\Health\Application\PingDatabase\Query\PingDatabaseDTO;
use Twitter\Health\Application\PingDatabase\Query\PingDatabaseQuery;
use Twitter\Health\Application\PingDatabase\Query\PingDatabaseQueryHandler;

class PingDatabaseQueryHandlerTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private PingDatabaseQueryHandler $handler;
    private Connection&MockObject $connection;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->connection = $this->createMock(Connection::class);
        $this->entityManager->method('getConnection')->willReturn($this->connection);
        $this->handler = new PingDatabaseQueryHandler($this->entityManager);
    }

    public function testInvokeReturnsSuccessWhenDatabaseIsReachable(): void
    {
        $this->connection
            ->expects($this->once())
            ->method('fetchOne')
            ->with('SELECT 1')
            ->willReturn('1');

        $query = new PingDatabaseQuery();
        $result = ($this->handler)($query);

        $this->assertInstanceOf(PingDatabaseDTO::class, $result);
        $this->assertSame('connected', $result->status);
        $this->assertSame('Database is up and running', $result->message);
    }

    public function testInvokeReturnsErrorWhenDatabaseThrowsException(): void
    {
        $sql = 'SELECT 1';
        $expectedMessage = 'Connection refused';

        $exception = new class($expectedMessage) extends \Exception implements DBALException {
        };

        $this->connection
            ->expects($this->once())
            ->method('fetchOne')
            ->with($sql)
            ->willThrowException($exception);

        $result = ($this->handler)(new PingDatabaseQuery());

        self::assertInstanceOf(PingDatabaseDTO::class, $result);
        self::assertSame('error', $result->status);
        self::assertSame($expectedMessage, $result->message);
    }

    public function testInvokeCallsEntityManagerGetConnection(): void
    {
        $this->connection->method('fetchOne')->willReturn('1');
        $this->entityManager->expects($this->once())->method('getConnection')->willReturn($this->connection);
        $query = new PingDatabaseQuery();
        ($this->handler)($query);
    }
}
