<?php

declare(strict_types=1);

namespace Twitter\Tweet\Infrastructure\Service;

interface LikeBroadcasterInterface
{
    public function broadcast(string $tweetId, string $userId, int $likesCount): void;
}
