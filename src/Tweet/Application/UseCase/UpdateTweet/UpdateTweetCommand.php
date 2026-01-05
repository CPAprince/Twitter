<?php

declare(strict_types=1);

namespace Twitter\Tweet\Application\UseCase\UpdateTweet;

final readonly class UpdateTweetCommand
{
    public function __construct(
        public string $tweetId,
        public string $content,
    ) {}
}
