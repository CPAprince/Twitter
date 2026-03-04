<?php

declare(strict_types=1);

namespace Twitter\Tests\Tweet\Application\Message;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twitter\Tweet\Application\Message\UpdateTweetLikesCountHandler;
use Twitter\Tweet\Application\Message\UpdateTweetLikesCountMessage;
use Twitter\Tweet\Infrastructure\Service\LikeBroadcasterInterface;
use Twitter\Tweet\Infrastructure\Service\TweetLikesCountUpdaterInterface;

#[Group('unit')]
#[CoversClass(UpdateTweetLikesCountHandler::class)]
final class UpdateTweetLikesCountHandlerTest extends TestCase
{
    private TweetLikesCountUpdaterInterface&MockObject $likesCountUpdater;
    private LikeBroadcasterInterface&MockObject $likeBroadcaster;
    private UpdateTweetLikesCountHandler $handler;

    protected function setUp(): void
    {
        $this->likesCountUpdater = $this->createMock(TweetLikesCountUpdaterInterface::class);
        $this->likeBroadcaster = $this->createMock(LikeBroadcasterInterface::class);

        $this->handler = new UpdateTweetLikesCountHandler(
            $this->likesCountUpdater,
            $this->likeBroadcaster,
        );
    }

    #[Test]
    public function invokeCallsUpdaterAndBroadcasterWhenTweetExists(): void
    {
        $tweetId = '019b5f3f-d110-7908-9177-5df439942a8b';
        $userId = '550e8400-e29b-41d4-a716-446655440000';
        $message = new UpdateTweetLikesCountMessage($tweetId, $userId, 1);

        $this->likesCountUpdater
            ->expects(self::once())
            ->method('updateCount')
            ->with($tweetId, 1)
            ->willReturn(5);

        $this->likeBroadcaster
            ->expects(self::once())
            ->method('broadcast')
            ->with($tweetId, $userId, 5);

        ($this->handler)($message);
    }

    #[Test]
    public function invokeDoesNotCallBroadcasterWhenTweetNotFound(): void
    {
        $tweetId = '019b5f3f-d110-7908-9177-5df439942a8b';
        $userId = '550e8400-e29b-41d4-a716-446655440000';
        $message = new UpdateTweetLikesCountMessage($tweetId, $userId, -1);

        $this->likesCountUpdater
            ->expects(self::once())
            ->method('updateCount')
            ->with($tweetId, -1)
            ->willReturn(null);

        $this->likeBroadcaster
            ->expects(self::never())
            ->method('broadcast');

        ($this->handler)($message);
    }

    #[Test]
    public function invokePassesCorrectDeltaForUnlike(): void
    {
        $tweetId = '019b5f3f-d110-7908-9177-5df439942a8b';
        $userId = '550e8400-e29b-41d4-a716-446655440000';
        $message = new UpdateTweetLikesCountMessage($tweetId, $userId, -1);

        $this->likesCountUpdater
            ->expects(self::once())
            ->method('updateCount')
            ->with($tweetId, -1)
            ->willReturn(4);

        $this->likeBroadcaster
            ->expects(self::once())
            ->method('broadcast')
            ->with($tweetId, $userId, 4);

        ($this->handler)($message);
    }
}
