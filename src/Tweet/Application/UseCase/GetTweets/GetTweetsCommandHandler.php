<?php

declare(strict_types=1);

namespace Twitter\Tweet\Application\UseCase\GetTweets;

use Twitter\Profile\Domain\Profile\Model\ProfileRepository;
use Twitter\Tweet\Application\UseCase\Shared\PaginationMeta;
use Twitter\Tweet\Application\UseCase\Shared\TweetResponse;
use Twitter\Tweet\Domain\Tweet\Model\Tweet;
use Twitter\Tweet\Domain\Tweet\Model\TweetRepository;

final readonly class GetTweetsCommandHandler
{
    public function __construct(
        private TweetRepository $tweetRepository,
        private ProfileRepository $profileRepository,
    ) {}

    public function handle(GetTweetsCommand $command): GetTweetsResponse
    {
        $offset = ($command->page - 1) * $command->limit;
        $tweets = $this->tweetRepository->getAllTweets($command->limit, $offset);
        $totalTweets = $this->tweetRepository->countTweets();

        $userIds = array_map(static fn (Tweet $tweet): string => $tweet->userId(), $tweets);
        $authorNames = $this->profileRepository->getNamesByUserIds(array_unique($userIds));

        $tweetResponses = array_map(
            static fn (Tweet $tweet): TweetResponse => new TweetResponse(
                id: $tweet->id(),
                content: $tweet->content(),
                createdAt: $tweet->createdAt(),
                updatedAt: $tweet->updatedAt(),
                authorId: $tweet->userId(),
                authorName: $authorNames[$tweet->userId()] ?? 'Unknown',
                likesCount: $tweet->likes(),
            ),
            $tweets,
        );

        return new GetTweetsResponse(
            $tweetResponses,
            new PaginationMeta($command->page, $command->limit, $totalTweets),
        );
    }
}
