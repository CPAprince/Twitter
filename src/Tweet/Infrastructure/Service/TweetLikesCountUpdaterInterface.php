<?php

declare(strict_types=1);

namespace Twitter\Tweet\Infrastructure\Service;

interface TweetLikesCountUpdaterInterface
{
    /**
     * Atomically update likes_count and return the new value.
     * Returns null if the tweet does not exist.
     */
    public function updateCount(string $tweetId, int $delta): ?int;
}
