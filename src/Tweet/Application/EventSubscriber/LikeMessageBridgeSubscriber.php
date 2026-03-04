<?php

declare(strict_types=1);

namespace Twitter\Tweet\Application\EventSubscriber;

use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Twitter\Like\Domain\Like\Event\TweetWasLiked;
use Twitter\Like\Domain\Like\Event\TweetWasUnliked;
use Twitter\Tweet\Application\Message\UpdateTweetLikesCountMessage;

final readonly class LikeMessageBridgeSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {}

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            TweetWasLiked::class => 'onTweetLiked',
            TweetWasUnliked::class => 'onTweetUnliked',
        ];
    }

    public function onTweetLiked(TweetWasLiked $event): void
    {
        $this->messageBus->dispatch(
            new UpdateTweetLikesCountMessage($event->tweetId, $event->userId, 1),
        );
    }

    public function onTweetUnliked(TweetWasUnliked $event): void
    {
        $this->messageBus->dispatch(
            new UpdateTweetLikesCountMessage($event->tweetId, $event->userId, -1),
        );
    }
}
