<?php

declare(strict_types=1);

namespace Twitter\Tests\Tweet\Infrastructure\Persistence\MySQL\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twitter\Tweet\Domain\Tweet\Exception\TweetNotFoundException;
use Twitter\Tweet\Domain\Tweet\Model\Tweet;
use Twitter\Tweet\Infrastructure\Persistence\MySQL\Repository\MySQLTweetRepository;

#[Group('unit')]
#[CoversClass(MySQLTweetRepository::class)]
final class MySQLTweetRepositoryTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private MySQLTweetRepository $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = new MySQLTweetRepository($this->entityManager);
    }

    #[Test]
    public function getByIdReturnsTweetWhenItExists(): void
    {
        $userId = '019b5f3f-d110-7908-9177-5df439942a8b';
        $tweet = Tweet::create($userId, 'Hello');
        $tweetId = $tweet->id();

        $this->entityManager
            ->expects(self::once())
            ->method('find')
            ->with(Tweet::class, $tweetId)
            ->willReturn($tweet);

        $result = $this->repository->getById($tweetId);

        self::assertSame($tweet, $result);
    }

    #[Test]
    public function getByIdThrowsTweetNotFoundExceptionWhenTweetDoesNotExist(): void
    {
        $tweetId = '019b5f41-0e5b-7f65-8b7a-0f9c0b3b3c11';

        $this->entityManager
            ->expects(self::once())
            ->method('find')
            ->with(Tweet::class, $tweetId)
            ->willReturn(null);

        $this->expectException(TweetNotFoundException::class);

        $this->repository->getById($tweetId);
    }

    #[Test]
    public function countTweetsReturnsTotalNumberOfTweets(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        $this->entityManager
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder
            ->expects(self::once())
            ->method('select')
            ->with('COUNT(t.id)')
            ->willReturnSelf();

        $queryBuilder
            ->expects(self::once())
            ->method('from')
            ->with(Tweet::class, 't')
            ->willReturnSelf();

        $queryBuilder
            ->expects(self::once())
            ->method('getQuery')
            ->willReturn($query);

        $query
            ->expects(self::once())
            ->method('getSingleScalarResult')
            ->willReturn(10);

        $result = $this->repository->countTweets();

        self::assertSame(10, $result);
    }
}
