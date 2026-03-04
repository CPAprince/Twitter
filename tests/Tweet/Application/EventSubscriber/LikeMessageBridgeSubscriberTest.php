<?php

declare(strict_types=1);

namespace Twitter\Tests\Tweet\Application\EventSubscriber;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Twitter\Like\Domain\Like\Event\TweetWasLiked;
use Twitter\Like\Domain\Like\Event\TweetWasUnliked;
use Twitter\Tweet\Application\EventSubscriber\LikeMessageBridgeSubscriber;
use Twitter\Tweet\Application\Message\UpdateTweetLikesCountMessage;

#[Group('unit')]
#[CoversClass(LikeMessageBridgeSubscriber::class)]
final class LikeMessageBridgeSubscriberTest extends TestCase
{
    private MessageBusInterface&MockObject $messageBus;
    private LikeMessageBridgeSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->subscriber = new LikeMessageBridgeSubscriber($this->messageBus);
    }

    #[Test]
    public function getSubscribedEventsReturnsCorrectMapping(): void
    {
        $events = LikeMessageBridgeSubscriber::getSubscribedEvents();

        self::assertSame([
            TweetWasLiked::class => 'onTweetLiked',
            TweetWasUnliked::class => 'onTweetUnliked',
        ], $events);
    }

    #[Test]
    public function onTweetLikedDispatchesMessageWithDeltaOne(): void
    {
        $tweetId = '019b5f3f-d110-7908-9177-5df439942a8b';
        $userId = '550e8400-e29b-41d4-a716-446655440000';
        $event = new TweetWasLiked($tweetId, $userId);

        $this->messageBus
            ->expects(self::once())
            ->method('dispatch')
            ->with(
                self::callback(function (UpdateTweetLikesCountMessage $message): bool {
                    return '019b5f3f-d110-7908-9177-5df439942a8b' === $message->tweetId
                        && '550e8400-e29b-41d4-a716-446655440000' === $message->userId
                        && 1 === $message->delta;
                }),
            )
            ->willReturn(new Envelope(new stdClass()));

        $this->subscriber->onTweetLiked($event);
    }

    #[Test]
    public function onTweetUnlikedDispatchesMessageWithDeltaMinusOne(): void
    {
        $tweetId = '019b5f3f-d110-7908-9177-5df439942a8b';
        $userId = '550e8400-e29b-41d4-a716-446655440000';
        $event = new TweetWasUnliked($tweetId, $userId);

        $this->messageBus
            ->expects(self::once())
            ->method('dispatch')
            ->with(
                self::callback(function (UpdateTweetLikesCountMessage $message): bool {
                    return '019b5f3f-d110-7908-9177-5df439942a8b' === $message->tweetId
                        && '550e8400-e29b-41d4-a716-446655440000' === $message->userId
                        && -1 === $message->delta;
                }),
            )
            ->willReturn(new Envelope(new stdClass()));

        $this->subscriber->onTweetUnliked($event);
    }
}
