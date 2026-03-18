<?php

declare(strict_types=1);

namespace Twitter\tests\Like\Application\UseCase\ToggleLike;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\SharedLockInterface;
use Twitter\Like\Application\UseCase\ToggleLike\LockingToggleLikeCommandHandler;
use Twitter\Like\Application\UseCase\ToggleLike\ToggleLikeCommand;
use Twitter\Like\Application\UseCase\ToggleLike\ToggleLikeCommandHandlerInterface;
use Twitter\Like\Application\UseCase\ToggleLike\ToggleLikeCommandResult;
use Twitter\Like\Domain\Like\Exception\LikeActionLockedException;

final class LockingToggleLikeCommandHandlerTest extends TestCase
{
    public function testThrowsExceptionWhenLockIsNotAcquired(): void
    {
        $command = new ToggleLikeCommand(
            'tweet-123',
            'user-456',
        );

        $innerHandler = $this->createMock(ToggleLikeCommandHandlerInterface::class);
        $innerHandler
            ->expects($this->never())
            ->method('handle');

        $lock = $this->createMock(SharedLockInterface::class);
        $lock
            ->expects($this->once())
            ->method('acquire')
            ->with(false)
            ->willReturn(false);

        $lock
            ->expects($this->never())
            ->method('release');

        $lockFactory = $this->createMock(LockFactory::class);
        $lockFactory
            ->expects($this->once())
            ->method('createLock')
            ->with(
                'lock_like_user_user-456_tweet_tweet-123',
                5,
            )
            ->willReturn($lock);

        $handler = new LockingToggleLikeCommandHandler(
            $innerHandler,
            $lockFactory,
            5,
        );

        $this->expectException(LikeActionLockedException::class);

        $handler->handle($command);
    }

    public function testDelegatesToInnerHandlerWhenLockIsAcquired(): void
    {
        $command = new ToggleLikeCommand(
            'tweet-123',
            'user-456',
        );

        $expectedResult = new ToggleLikeCommandResult(true);

        $innerHandler = $this->createMock(ToggleLikeCommandHandlerInterface::class);
        $innerHandler
            ->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willReturn($expectedResult);

        $lock = $this->createMock(SharedLockInterface::class);
        $lock
            ->expects($this->once())
            ->method('acquire')
            ->with(false)
            ->willReturn(true);

        $lock
            ->expects($this->once())
            ->method('release');

        $lockFactory = $this->createMock(LockFactory::class);
        $lockFactory
            ->expects($this->once())
            ->method('createLock')
            ->with(
                'lock_like_user_user-456_tweet_tweet-123',
                5,
            )
            ->willReturn($lock);

        $handler = new LockingToggleLikeCommandHandler(
            $innerHandler,
            $lockFactory,
            5,
        );

        $result = $handler->handle($command);

        self::assertSame($expectedResult, $result);
    }

    public function testReleasesLockWhenInnerHandlerThrows(): void
    {
        $command = new ToggleLikeCommand(
            'tweet-123',
            'user-456',
        );

        $innerHandler = $this->createMock(ToggleLikeCommandHandlerInterface::class);
        $innerHandler
            ->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willThrowException(new RuntimeException('Boom'));

        $lock = $this->createMock(SharedLockInterface::class);
        $lock
            ->expects($this->once())
            ->method('acquire')
            ->with(false)
            ->willReturn(true);

        $lock
            ->expects($this->once())
            ->method('release');

        $lockFactory = $this->createMock(LockFactory::class);
        $lockFactory
            ->expects($this->once())
            ->method('createLock')
            ->with(
                'lock_like_user_user-456_tweet_tweet-123',
                5,
            )
            ->willReturn($lock);

        $handler = new LockingToggleLikeCommandHandler(
            $innerHandler,
            $lockFactory,
            5,
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Boom');

        $handler->handle($command);
    }
}
