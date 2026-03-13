<?php

declare(strict_types=1);

namespace Twitter\Tests\Tweet\Infrastructure\Service;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Twitter\Tweet\Infrastructure\Service\TweetLikesCountUpdater;

#[Group('unit')]
#[CoversClass(TweetLikesCountUpdater::class)]
final class TweetLikesCountUpdaterTest extends TestCase
{
    private Connection&MockObject $connection;
    private LoggerInterface&MockObject $logger;
    private TweetLikesCountUpdater $updater;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->updater = new TweetLikesCountUpdater($this->connection, $this->logger);
    }

    #[Test]
    #[AllowMockObjectsWithoutExpectations]
    public function updateCountReturnsNewCountWhenTweetExists(): void
    {
        $tweetId = '019b5f3f-d110-7908-9177-5df439942a8b';

        $this->connection
            ->expects(self::once())
            ->method('executeStatement')
            ->with(
                self::stringContains(/* @lang text */ 'UPDATE tweets SET likes_count'),
                self::callback(function (array $params): bool {
                    return isset($params['delta']) && 1 === $params['delta']
                        && isset($params['id']);
                }),
            )
            ->willReturn(1);

        $this->connection
            ->expects(self::once())
            ->method('fetchOne')
            ->willReturn('6');

        $result = $this->updater->updateCount($tweetId, 1);

        self::assertSame(6, $result);
    }

    #[Test]
    public function updateCountReturnsNullWhenTweetNotFound(): void
    {
        $tweetId = '019b5f3f-d110-7908-9177-5df439942a8b';

        $this->connection
            ->expects(self::once())
            ->method('executeStatement')
            ->willReturn(0);

        $this->logger
            ->expects(self::once())
            ->method('warning')
            ->with('Tweet not found for likes count update', ['tweetId' => $tweetId]);

        $this->connection
            ->expects(self::never())
            ->method('fetchOne');

        $result = $this->updater->updateCount($tweetId, -1);

        self::assertNull($result);
    }

    #[Test]
    #[AllowMockObjectsWithoutExpectations]
    public function updateCountPassesCorrectBinaryId(): void
    {
        $tweetId = '019b5f3f-d110-7908-9177-5df439942a8b';
        $expectedBinary = pack('H*', str_replace('-', '', $tweetId));

        $callCount = 0;
        $this->connection
            ->method('executeStatement')
            ->willReturnCallback(function (string $sql, array $params) use ($expectedBinary, &$callCount): int {
                ++$callCount;
                if (isset($params['id'])) {
                    self::assertSame($expectedBinary, $params['id']);
                }

                return 1;
            });

        $this->connection
            ->method('fetchOne')
            ->willReturn('3');

        $this->updater->updateCount($tweetId, 1);

        self::assertSame(1, $callCount);
    }
}
