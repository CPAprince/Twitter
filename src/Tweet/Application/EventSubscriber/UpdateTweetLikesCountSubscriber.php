<?php

declare(strict_types=1);

namespace Twitter\Tweet\Application\EventSubscriber;

use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twitter\Like\Domain\Like\Event\TweetWasLiked;
use Twitter\Like\Domain\Like\Event\TweetWasUnliked;
use Twitter\Tweet\Domain\Tweet\Exception\TweetNotFoundException;
use Twitter\Tweet\Domain\Tweet\Model\TweetRepository;

final readonly class UpdateTweetLikesCountSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TweetRepository $tweetRepository,
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
        try {
            $tweet = $this->tweetRepository->getById($event->tweetId);

            $tweet->increaseLikesCount();
            $this->tweetRepository->add($tweet);
        } catch (TweetNotFoundException) {
            return;
        }
    }

    public function onTweetUnliked(TweetWasUnliked $event): void
    {
        try {
            $tweet = $this->tweetRepository->getById($event->tweetId);

            $tweet->decreaseLikesCount();
            $this->tweetRepository->add($tweet);
        } catch (TweetNotFoundException) {
            return;
        }
    }
}
