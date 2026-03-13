<?php

declare(strict_types=1);

namespace Twitter\Tests\Profile\Infrastructure\Cache\Proxy;

use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Twitter\Profile\Application\UseCase\GetProfile\GetProfileQuery;
use Twitter\Profile\Application\UseCase\GetProfile\GetProfileQueryHandlerInterface;
use Twitter\Profile\Application\UseCase\GetProfile\GetProfileQueryResult;
use Twitter\Profile\Infrastructure\Cache\Proxy\GetProfileQueryHandlerProxy;

#[Group('unit')]
#[CoversClass(GetProfileQueryHandlerProxy::class)]
final class GetProfileQueryHandlerProxyTest extends TestCase
{
    private GetProfileQueryHandlerInterface&MockObject $inner;
    private TagAwareAdapter $cache;
    private GetProfileQueryHandlerProxy $proxy;
    private int $ttl = 3600;

    protected function setUp(): void
    {
        $this->inner = $this->createMock(GetProfileQueryHandlerInterface::class);
        $this->cache = new TagAwareAdapter(new ArrayAdapter());
        $this->proxy = new GetProfileQueryHandlerProxy($this->inner, $this->cache, $this->ttl);
    }

    #[Test]
    #[DataProvider('invalidationTagsProvider')]
    public function itCachesResultsAndInvalidatesViaTags(
        GetProfileQuery $query,
        GetProfileQueryResult $expectedResult,
        string $tagToInvalidate,
    ): void {
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

        $this->cache->invalidateTags([$tagToInvalidate]);

        $result3 = $this->proxy->handle($query);
        self::assertEquals($expectedResult, $result3);
    }

    public static function invalidationTagsProvider(): Generator
    {
        $userId = '019b5f3f-d110-7908-9177-5df439942a8b';
        $query = new GetProfileQuery(userId: $userId);
        $result = new GetProfileQueryResult(
            name: 'John Doe',
            bio: 'Software Engineer',
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );

        yield 'specific user profile tag' => [$query, $result, sprintf('profile_%s', $userId)];
        yield 'general profile tag' => [$query, $result, 'profile'];
    }
}
