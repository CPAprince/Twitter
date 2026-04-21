<?php

declare(strict_types=1);

namespace Twitter\Tweet\Application\Message;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Twitter\Tweet\Infrastructure\Service\LikeBroadcasterInterface;
use Twitter\Tweet\Infrastructure\Service\TweetLikesCountUpdaterInterface;

#[AsMessageHandler]
final readonly class UpdateTweetLikesCountHandler
{
    public function __construct(
        private TweetLikesCountUpdaterInterface $likesCountUpdater,
        private LikeBroadcasterInterface $likeBroadcaster,
    ) {}

    public function __invoke(UpdateTweetLikesCountMessage $message): void
    {
        $newCount = $this->likesCountUpdater->updateCount(
            $message->tweetId,
            $message->delta,
        );

        if (null !== $newCount) {
            $this->likeBroadcaster->broadcast(
                $message->tweetId,
                $message->userId,
                $newCount,
            );
        }
    }
}
