<?php

declare(strict_types=1);

namespace Twitter\Tweet\Application\UseCase\GetUserTweets;

use Twitter\Tweet\Application\UseCase\Shared\PaginationMeta;
use Twitter\Tweet\Domain\Tweet\Model\Tweet;
use Twitter\Tweet\Domain\Tweet\Model\TweetRepository;

final readonly class GetUserTweetsQueryHandler
{
    public function __construct(private TweetRepository $tweetRepository) {}

    public function handle(GetUserTweetsQuery $query): GetUserTweetsQueryResult
    {
        $offset = ($query->page - 1) * $query->limit;
        $tweets = $this->tweetRepository->getByUserId($query->userId, $query->limit, $offset);
        $totalTweets = $this->tweetRepository->countByUserId($query->userId);

        return new GetUserTweetsQueryResult(
            $tweets,
            new PaginationMeta($query->page, $query->limit, $totalTweets),
        );
    }
}
