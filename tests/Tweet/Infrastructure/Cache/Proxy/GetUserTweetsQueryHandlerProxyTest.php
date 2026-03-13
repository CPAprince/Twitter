<?php

declare(strict_types=1);

namespace Twitter\Tests\Tweet\Infrastructure\Cache\Proxy;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Twitter\Tweet\Application\UseCase\GetUserTweets\GetUserTweetsQuery;
use Twitter\Tweet\Application\UseCase\GetUserTweets\GetUserTweetsQueryHandlerInterface;
use Twitter\Tweet\Application\UseCase\GetUserTweets\GetUserTweetsQueryResult;
use Twitter\Tweet\Infrastructure\Cache\Proxy\GetUserTweetsQueryHandlerProxy;

#[Group('unit')]
#[CoversClass(GetUserTweetsQueryHandlerProxy::class)]
final class GetUserTweetsQueryHandlerProxyTest extends TestCase
{
    private GetUserTweetsQueryHandlerInterface&MockObject $inner;
    private TagAwareAdapter $cache;
    private GetUserTweetsQueryHandlerProxy $proxy;
    private int $ttl = 600;

    protected function setUp(): void
    {
        $this->inner = $this->createMock(GetUserTweetsQueryHandlerInterface::class);
        $this->cache = new TagAwareAdapter(new ArrayAdapter());
        $this->proxy = new GetUserTweetsQueryHandlerProxy($this->inner, $this->cache, $this->ttl);
    }

    #[Test]
    public function itCachesResultsAndInvalidatesViaSpecificUserTag(): void
    {
        $userId = '019b5f3f-d110-7908-9177-5df439942a8b';
        $query = new GetUserTweetsQuery(userId: $userId, limit: 10, page: 1);
        $expectedResult = new GetUserTweetsQueryResult([]);

        // Expected to be called twice: 1st for miss, 2nd after invalidation
        $this->inner
            ->expects(self::exactly(2))
            ->method('handle')
            ->with($query)
            ->willReturn($expectedResult);

        $result1 = $this->proxy->handle($query);
        self::assertEquals($expectedResult, $result1);

        $result2 = $this->proxy->handle($query);
        self::assertEquals($expectedResult, $result2);

        $this->cache->invalidateTags([sprintf('user_tweets_%s', $userId)]);

        $result3 = $this->proxy->handle($query);
        self::assertEquals($expectedResult, $result3);
    }

    #[Test]
    public function itInvalidatesViaGeneralUserTweetsTag(): void
    {
        $userId = '019b5f3f-d110-7908-9177-5df439942a8b';
        $query = new GetUserTweetsQuery(userId: $userId, limit: 10, page: 1);
        $expectedResult = new GetUserTweetsQueryResult([]);

        $this->inner
            ->expects(self::exactly(2))
            ->method('handle')
            ->willReturn($expectedResult);

        $this->proxy->handle($query);
        $this->proxy->handle($query);

        $this->cache->invalidateTags(['user_tweets']);

        $this->proxy->handle($query);
    }
}
