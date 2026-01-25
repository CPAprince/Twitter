<?php

declare(strict_types=1);

namespace Twitter\Tests\Tweet\Application\UseCase\GetUserTweets;

use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twitter\Tweet\Application\UseCase\GetUserTweets\GetUserTweetsQuery;
use Twitter\Tweet\Application\UseCase\GetUserTweets\GetUserTweetsQueryHandler;
use Twitter\Tweet\Application\UseCase\Shared\PaginationMeta;
use Twitter\Tweet\Domain\Tweet\Model\Tweet;
use Twitter\Tweet\Domain\Tweet\Model\TweetRepository;

#[Group('unit')]
#[CoversMethod(GetUserTweetsQueryHandler::class, 'handle')]
final class GetUserTweetsQueryHandlerTest extends TestCase
{
    private GetUserTweetsQueryHandler $handler;
    private MockObject|TweetRepository $tweetRepository;

    protected function setUp(): void
    {
        $this->tweetRepository = $this->createMock(TweetRepository::class);
        $this->handler = new GetUserTweetsQueryHandler($this->tweetRepository);
    }

    #[Test]
    public function returnsTweetsAndMetaWhenExist(): void
    {
        $userId = '019b5f3f-d110-7908-9177-5df439942a8b';
        $tweet = Tweet::create($userId, 'Hello World');

        $this->tweetRepository
            ->expects(self::once())
            ->method('getByUserId')
            ->with($userId, 20, 0)
            ->willReturn([$tweet]);

        $this->tweetRepository
            ->expects(self::once())
            ->method('countByUserId')
            ->with($userId)
            ->willReturn(1);

        $query = new GetUserTweetsQuery($userId, limit: 20, page: 1);
        $result = $this->handler->handle($query);

        self::assertCount(1, $result->tweets);
        self::assertSame($tweet, $result->tweets[0]);
        self::assertInstanceOf(PaginationMeta::class, $result->meta);
        self::assertSame(1, $result->meta->page);
        self::assertSame(20, $result->meta->limit);
        self::assertSame(1, $result->meta->totalItems);
    }

    #[Test]
    public function calculatesOffsetCorrectly(): void
    {
        $userId = '019b5f3f-d110-7908-9177-5df439942a8b';

        $this->tweetRepository
            ->expects(self::once())
            ->method('getByUserId')
            ->with($userId, 10, 20)
            ->willReturn([]);

        $this->tweetRepository
            ->expects(self::once())
            ->method('countByUserId')
            ->with($userId)
            ->willReturn(0);

        $query = new GetUserTweetsQuery($userId, limit: 10, page: 3);
        $result = $this->handler->handle($query);

        self::assertEmpty($result->tweets);
        self::assertSame(3, $result->meta->page);
        self::assertSame(10, $result->meta->limit);
        self::assertSame(0, $result->meta->totalItems);
    }

    #[Test]
    public function returnsEmptyListWhenNoTweetsMatch(): void
    {
        $userId = '019b5f3f-d110-7908-9177-5df439942a8b';

        $this->tweetRepository
            ->expects(self::once())
            ->method('getByUserId')
            ->with($userId, 20, 0)
            ->willReturn([]);

        $this->tweetRepository
            ->expects(self::once())
            ->method('countByUserId')
            ->with($userId)
            ->willReturn(0);

        $query = new GetUserTweetsQuery($userId, limit: 20, page: 1);
        $result = $this->handler->handle($query);

        self::assertEmpty($result->tweets);
        self::assertSame(0, $result->meta->totalItems);
    }
}
