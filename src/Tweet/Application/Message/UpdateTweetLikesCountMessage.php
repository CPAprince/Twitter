<?php

declare(strict_types=1);

namespace Twitter\Tweet\Application\Message;

final readonly class UpdateTweetLikesCountMessage
{
    public function __construct(
        public string $tweetId,
        public string $userId,
        public int $delta,
    ) {}
}
