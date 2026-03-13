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
use Twitter\Tweet\Application\UseCase\CreateTweet\CreateTweetCommand;
use Twitter\Tweet\Application\UseCase\CreateTweet\CreateTweetCommandHandlerInterface;
use Twitter\Tweet\Application\UseCase\CreateTweet\CreateTweetCommandResult;
use Twitter\Tweet\Domain\Tweet\Exception\UserNotFoundException;
use Twitter\Tweet\Infrastructure\Cache\Decorator\CreateTweetCommandHandlerDecorator;

#[Group('unit')]
#[CoversClass(CreateTweetCommandHandlerDecorator::class)]
final class CreateTweetCommandHandlerDecoratorTest extends TestCase
{
    private CreateTweetCommandHandlerInterface&MockObject $inner;
    private TagAwareCacheInterface&MockObject $cache;
    private CreateTweetCommandHandlerDecorator $decorator;

    protected function setUp(): void
    {
        $this->inner = $this->createMock(CreateTweetCommandHandlerInterface::class);
        $this->cache = $this->createMock(TagAwareCacheInterface::class);
        $this->decorator = new CreateTweetCommandHandlerDecorator($this->inner, $this->cache);
    }

    #[Test]
    public function itDelegatesToInnerAndInvalidatesCacheOnSuccess(): void
    {
        $userId = '019b5f3f-d110-7908-9177-5df439942a8b';
        $tweetId = '019b5f3f-d110-7908-9177-5df439942a8c';

        $command = new CreateTweetCommand($userId, 'Some tweet content');
        $expectedResult = new CreateTweetCommandResult($tweetId, new DateTimeImmutable(), new DateTimeImmutable());

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
        $command = new CreateTweetCommand($userId, 'Faulty content');

        $this->inner
            ->expects(self::once())
            ->method('handle')
            ->willThrowException(new UserNotFoundException($userId));

        $this->cache
            ->expects(self::never())
            ->method('invalidateTags');

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage(sprintf('User with id "%s" not found', $userId));

        $this->decorator->handle($command);
    }
}
