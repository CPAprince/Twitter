<?php

declare(strict_types=1);

namespace Twitter\Tests\Tweet\Domain\Tweet\Model;

use Assert\LazyAssertionException;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twitter\Tweet\Domain\Tweet\Model\Tweet;

#[Group('unit')]
#[CoversClass(Tweet::class)]
final class TweetTest extends TestCase
{
    #[Test]
    public function createSucceedsWithValidData(): void
    {
        $userId = '550e8400-e29b-41d4-a716-446655440000';
        $content = 'Hello world';

        $tweet = Tweet::create($userId, $content);

        self::assertSame($userId, $tweet->userId());
        self::assertSame($content, $tweet->content());
        self::assertNotEmpty($tweet->id());
        self::assertInstanceOf(DateTimeImmutable::class, $tweet->createdAt());
        self::assertInstanceOf(DateTimeImmutable::class, $tweet->updatedAt());
    }

    #[Test]
    #[DataProvider('invalidUserIdProvider')]
    public function createFailsWithInvalidUserId(string $userId): void
    {
        $this->expectException(LazyAssertionException::class);

        Tweet::create($userId, 'Valid content');
    }

    public static function invalidUserIdProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'whitespace only' => ['  '];
        yield 'random string' => ['abc123?'];
    }

    #[Test]
    #[DataProvider('invalidContentProvider')]
    public function createFailsWithInvalidContent(string $content): void
    {
        $this->expectException(LazyAssertionException::class);

        Tweet::create(
            '550e8400-e29b-41d4-a716-446655440000',
            $content
        );
    }

    public static function invalidContentProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'whitespace only' => ['  '];
        yield 'too long content' => [str_repeat('a', 281)];
    }
}
