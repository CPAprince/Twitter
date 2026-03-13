<?php

declare(strict_types=1);

namespace Twitter\Tests\Tweet\Infrastructure\Cache\Decorator;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Twitter\Tweet\Application\UseCase\UpdateTweet\UpdateTweetCommand;
use Twitter\Tweet\Application\UseCase\UpdateTweet\UpdateTweetCommandHandlerInterface;
use Twitter\Tweet\Application\UseCase\UpdateTweet\UpdateTweetCommandResult;
use Twitter\Tweet\Domain\Tweet\Exception\TweetNotFoundException;
use Twitter\Tweet\Infrastructure\Cache\Decorator\UpdateTweetCommandHandlerDecorator;

#[Group('unit')]
#[CoversClass(UpdateTweetCommandHandlerDecorator::class)]
final class UpdateTweetCommandHandlerDecoratorTest extends TestCase
{
    private UpdateTweetCommandHandlerInterface&MockObject $inner;
    private TagAwareCacheInterface&MockObject $cache;
    private UpdateTweetCommandHandlerDecorator $decorator;

    protected function setUp(): void
    {
        $this->inner = $this->createMock(UpdateTweetCommandHandlerInterface::class);
        $this->cache = $this->createMock(TagAwareCacheInterface::class);
        $this->decorator = new UpdateTweetCommandHandlerDecorator($this->inner, $this->cache);
    }

    #[Test]
    public function itDelegatesToInnerAndInvalidatesCacheOnSuccess(): void
    {
        $userId = '019b5f3f-d110-7908-9177-5df439942a8b';
        $tweetId = '019b5f3f-d110-7908-9177-5df439942a8c';

        $command = new UpdateTweetCommand($userId, $tweetId, 'Updated content');
        $expectedResult = new UpdateTweetCommandResult('Updated content', new DateTimeImmutable());

        $this->inner
            ->expects(self::once())
            ->method('handle')
            ->with($command)
            ->willReturn($expectedResult);

        $this->cache
            ->expects(self::once())
            ->method('invalidateTags')
            ->with([sprintf('user_tweets_%s', $userId)]);

        $result = $this->decorator->handle($command);

        self::assertSame($expectedResult, $result);
    }

    #[Test]
    public function itDoesNotInvalidateCacheIfInnerHandlerFails(): void
    {
        $userId = 'user-123';
        $tweetId = 'tweet-456';
        $command = new UpdateTweetCommand($userId, $tweetId, 'Faulty content');

        $this->inner
            ->expects(self::once())
            ->method('handle')
            ->willThrowException(new TweetNotFoundException($tweetId));

        $this->cache
            ->expects(self::never())
            ->method('invalidateTags');

        $this->expectException(TweetNotFoundException::class);
        $this->expectExceptionMessage(sprintf('Tweet "%s" not found.', $tweetId));

        $this->decorator->handle($command);
    }
}
