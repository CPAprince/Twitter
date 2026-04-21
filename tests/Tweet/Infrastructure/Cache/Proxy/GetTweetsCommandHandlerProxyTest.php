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
use Twitter\Tweet\Application\UseCase\GetTweets\GetTweetsCommand;
use Twitter\Tweet\Application\UseCase\GetTweets\GetTweetsCommandHandlerInterface;
use Twitter\Tweet\Application\UseCase\GetTweets\GetTweetsResponse;
use Twitter\Tweet\Infrastructure\Cache\Proxy\GetTweetsCommandHandlerProxy;

#[Group('unit')]
#[CoversClass(GetTweetsCommandHandlerProxy::class)]
final class GetTweetsCommandHandlerProxyTest extends TestCase
{
    private GetTweetsCommandHandlerInterface&MockObject $inner;
    private TagAwareAdapter $cache;
    private GetTweetsCommandHandlerProxy $proxy;
    private int $ttl = 60;

    protected function setUp(): void
    {
        $this->inner = $this->createMock(GetTweetsCommandHandlerInterface::class);
        $this->cache = new TagAwareAdapter(new ArrayAdapter());
        $this->proxy = new GetTweetsCommandHandlerProxy($this->inner, $this->cache, $this->ttl);
    }

    #[Test]
    public function itCachesResultsAndInvalidatesViaTags(): void
    {
        $command = new GetTweetsCommand(limit: 50, page: 2);
        $expectedResponse = new GetTweetsResponse([]);

        // Expected to be called twice: 1st for initial cache miss, 2nd after invalidation
        $this->inner
            ->expects(self::exactly(2))
            ->method('handle')
            ->with($command)
            ->willReturn($expectedResponse);

        $result1 = $this->proxy->handle($command);
        self::assertEquals($expectedResponse, $result1);

        $result2 = $this->proxy->handle($command);
        self::assertEquals($expectedResponse, $result2);

        $this->cache->invalidateTags(['tweets_main']);

        $result3 = $this->proxy->handle($command);
        self::assertEquals($expectedResponse, $result3);
    }
}
