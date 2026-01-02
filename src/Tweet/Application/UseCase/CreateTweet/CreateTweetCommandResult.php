<?php

declare(strict_types=1);

namespace Twitter\Tweet\Application\UseCase\CreateTweet;

final readonly class CreateTweetCommandResult
{
    public function __construct(
        public string $tweetId,
    ) {}
}
