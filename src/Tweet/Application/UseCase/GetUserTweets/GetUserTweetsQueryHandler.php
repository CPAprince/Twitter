<?php

declare(strict_types=1);

namespace Twitter\Tweet\Application\UseCase\GetUserTweets;

use Twitter\Tweet\Domain\Tweet\Model\Tweet;
use Twitter\Tweet\Domain\Tweet\Model\TweetRepository;

final readonly class GetUserTweetsQueryHandler
{
    public function __construct(private TweetRepository $tweetRepository) {}

    public function handle(GetUserTweetsQuery $query): GetUserTweetsQueryResult
    {
        $tweets = $this->tweetRepository->getAllTweets($query->limit, $query->page, $query->userId);

        $tweets = array_filter($tweets, fn (Tweet $tweet) => $tweet->userId() === $query->userId);

        $chunkLength = $query->page * $query->limit;
        if ($query->limit > 0 && $query->page >= 0 && $chunkLength <= count($tweets)) {
            $tweets = array_slice($tweets, $chunkLength, $query->limit);
        }

        return new GetUserTweetsQueryResult($tweets);
    }
}
