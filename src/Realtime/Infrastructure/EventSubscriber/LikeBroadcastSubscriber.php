<?php

declare(strict_types=1);

namespace Twitter\Realtime\Infrastructure\EventSubscriber;

use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Twitter\Like\Domain\Like\Event\TweetWasLiked;
use Twitter\Like\Domain\Like\Event\TweetWasUnliked;
use Twitter\Tweet\Domain\Tweet\Exception\TweetNotFoundException;
use Twitter\Tweet\Domain\Tweet\Model\TweetRepository;

final readonly class LikeBroadcastSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private HubInterface $hub,
        private TweetRepository $tweetRepository,
    ) {}

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            TweetWasLiked::class => ['onLikeChanged', -10],
            TweetWasUnliked::class => ['onLikeChanged', -10],
        ];
    }

    public function onLikeChanged(TweetWasLiked|TweetWasUnliked $event): void
    {
        try {
            $tweet = $this->tweetRepository->getById($event->tweetId);
        } catch (TweetNotFoundException) {
            return;
        }

        $update = new Update(
            topics: [sprintf('/tweets/%s/likes', $event->tweetId)],
            data: json_encode([
                'tweetId' => $event->tweetId,
                'likesCount' => $tweet->likes(),
            ], JSON_THROW_ON_ERROR),
        );

        $this->hub->publish($update);
    }
}
