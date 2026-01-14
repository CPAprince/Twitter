<?php

declare(strict_types=1);

namespace Twitter\Tests\Tweet\Application\EventSubscriber;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twitter\Like\Domain\Like\Event\TweetWasLiked;
use Twitter\Like\Domain\Like\Event\TweetWasUnliked;
use Twitter\Tweet\Application\EventSubscriber\UpdateTweetLikesCountSubscriber;
use Twitter\Tweet\Domain\Tweet\Exception\TweetNotFoundException;
use Twitter\Tweet\Domain\Tweet\Model\Tweet;
use Twitter\Tweet\Domain\Tweet\Model\TweetRepository;

#[Group('unit')]
#[CoversClass(UpdateTweetLikesCountSubscriber::class)]
final class UpdateTweetLikesCountSubscriberTest extends TestCase
{
    private UpdateTweetLikesCountSubscriber $subscriber;
    private TweetRepository&MockObject $tweetRepository;

    protected function setUp(): void
    {
        $this->tweetRepository = $this->createMock(TweetRepository::class);
        $this->subscriber = new UpdateTweetLikesCountSubscriber($this->tweetRepository);
    }

    #[Test]
    public function onTweetLikedIncreasesCountAndSavesTweet(): void
    {
        $tweetId = '019b5f3f-d110-7908-9177-5df439942a8b';
        $userId = '550e8400-e29b-41d4-a716-446655440000';
        $event = new TweetWasLiked($tweetId, $userId);

        $tweet = Tweet::create($userId, 'Content');

        $this->tweetRepository
            ->expects(self::once())
            ->method('getById')
            ->with($tweetId)
            ->willReturn($tweet);

        $this->tweetRepository
            ->expects(self::once())
            ->method('add')
            ->with(
                self::callback(function (Tweet $savedTweet): bool {
                    return 1 === $savedTweet->likesCount();
                })
            );

        $this->subscriber->onTweetLiked($event);
    }

    #[Test]
    public function onTweetUnlikedDecreasesCountAndSavesTweet(): void
    {
        $tweetId = '019b5f3f-d110-7908-9177-5df439942a8b';
        $userId = '550e8400-e29b-41d4-a716-446655440000';
        $event = new TweetWasUnliked($tweetId, $userId);

        $tweet = Tweet::create($userId, 'Content');
        $tweet->increaseLikesCount();

        $this->tweetRepository
            ->expects(self::once())
            ->method('getById')
            ->with($tweetId)
            ->willReturn($tweet);

        $this->tweetRepository
            ->expects(self::once())
            ->method('add')
            ->with(
                self::callback(function (Tweet $savedTweet): bool {
                    return 0 === $savedTweet->likesCount();
                })
            );

        $this->subscriber->onTweetUnliked($event);
    }

    #[Test]
    public function onTweetLikedDoesNotFailWhenTweetNotFound(): void
    {
        $tweetId = '019b5f3f-d110-7908-9177-5df439942a8b';
        $event = new TweetWasLiked($tweetId, 'some-user-id');

        $this->tweetRepository
            ->expects(self::once())
            ->method('getById')
            ->with($tweetId)
            ->willThrowException(new TweetNotFoundException($tweetId));

        $this->tweetRepository
            ->expects(self::never())
            ->method('add');

        $this->subscriber->onTweetLiked($event);
    }
}
