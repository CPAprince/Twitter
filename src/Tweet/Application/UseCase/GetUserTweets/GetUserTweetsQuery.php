<?php

declare(strict_types=1);

namespace Twitter\Tweet\Application\UseCase\GetUserTweets;

final readonly class GetUserTweetsQuery
{
    public function __construct(
        public string $userId,
        public int $limit = 20,
        public int $page = 1,
    ) {}
}
