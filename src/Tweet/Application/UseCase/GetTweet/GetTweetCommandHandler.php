<?php

declare(strict_types=1);

namespace Twitter\Tweet\Application\UseCase\GetTweet;

use http\Env\Response;
use Twitter\Profile\Domain\Profile\Model\ProfileRepository;
use Twitter\Tweet\Application\UseCase\Shared\TweetResponse;
use Twitter\Tweet\Domain\Tweet\Exception\TweetNotFoundException;
use Twitter\Tweet\Domain\Tweet\Model\TweetRepository;
use Twitter\Tweet\Domain\Tweet\Model\Tweet;

final readonly class GetTweetCommandHandler
{
    public function __construct(
        private TweetRepository $tweetRepository,
        private ProfileRepository $profileRepository,
    ) {}

    public function handle(GetTweetCommand $command): TweetResponse
    {
        $tweet = $this->tweetRepository->getById($command->tweetId);

        if($tweet->moderationStatus() !== Tweet::MODERATION_APPROVED) {
            throw new TweetNotFoundException($tweet->id());
        }

        $authorId = $tweet->userId();
        $profile = $this->profileRepository->getByUserId($authorId);

        return new TweetResponse(
            id: $tweet->id(),
            content: $tweet->content(),
            createdAt: $tweet->createdAt(),
            updatedAt: $tweet->updatedAt(),
            authorId: $authorId,
            authorName: $profile->name(),
            likesCount: $tweet->likes(),
        );
    }
}
