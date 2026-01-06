<?php

declare(strict_types=1);

namespace Twitter\Tests\Like\Application\UseCase\ToggleLike;

use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twitter\Like\Application\UseCase\ToggleLike\ToggleLikeCommand;
use Twitter\Like\Application\UseCase\ToggleLike\ToggleLikeCommandHandler;
use Twitter\Like\Domain\Like\Model\Like;
use Twitter\Like\Domain\Like\Model\LikeRepository;

#[Group('unit')]
#[CoversMethod(ToggleLikeCommandHandler::class, 'handle')]
final class ToggleLikeCommandHandlerTest extends TestCase
{
    private ToggleLikeCommandHandler $handler;
    private MockObject|LikeRepository $likeRepository;

    protected function setUp(): void
    {
        $this->likeRepository = $this->createMock(LikeRepository::class);
        $this->handler = new ToggleLikeCommandHandler($this->likeRepository);
    }

    #[Test]
    public function handleCreatesLikeWhenItDoesNotExist(): void
    {
        $tweetId = '019b5f3f-d110-7908-9177-5df439942a8b';
        $userId = '550e8400-e29b-41d4-a716-446655440000';

        $command = new ToggleLikeCommand(
            tweetId: $tweetId,
            userId: $userId,
        );

        $this->likeRepository
            ->expects(self::once())
            ->method('findOneByTweetAndUser')
            ->with($tweetId, $userId)
            ->willReturn(null);

        $this->likeRepository
            ->expects(self::once())
            ->method('add')
            ->with(
                self::callback(
                    fn (Like $like): bool => $like->tweetId() === $tweetId
                        && $like->userId() === $userId,
                ),
            );

        $this->likeRepository
            ->expects(self::never())
            ->method('remove');

        $result = $this->handler->handle($command);

        self::assertTrue($result->liked, 'Result should indicate that the tweet was liked');
    }

    #[Test]
    public function handleRemovesLikeWhenItAlreadyExists(): void
    {
        $tweetId = '019b5f3f-d110-7908-9177-5df439942a8b';
        $userId = '550e8400-e29b-41d4-a716-446655440000';

        $command = new ToggleLikeCommand(
            tweetId: $tweetId,
            userId: $userId,
        );

        $existingLike = Like::create($tweetId, $userId);

        $this->likeRepository
            ->expects(self::once())
            ->method('findOneByTweetAndUser')
            ->with($tweetId, $userId)
            ->willReturn($existingLike);

        $this->likeRepository
            ->expects(self::once())
            ->method('remove')
            ->with($existingLike);

        $this->likeRepository
            ->expects(self::never())
            ->method('add');

        $result = $this->handler->handle($command);

        self::assertFalse($result->liked, 'Result should indicate that the tweet was unliked');
    }
}
