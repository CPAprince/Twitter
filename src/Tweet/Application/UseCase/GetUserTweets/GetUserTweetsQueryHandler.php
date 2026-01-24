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
        return new GetUserTweetsQueryResult($tweets);
    }
}
