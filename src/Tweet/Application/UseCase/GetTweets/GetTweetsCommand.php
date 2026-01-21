<?php

declare(strict_types=1);

namespace Twitter\Tweet\Application\UseCase\GetTweets;

final readonly class GetTweetsCommand
{
    public function __construct(
        public int $page = 1,
        public int $limit = 20,
    ) {}
}
