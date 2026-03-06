<?php

declare(strict_types=1);

namespace Twitter\Tweet\Application\Moderation;

final readonly class ModerationBatchItem
{
    public function __construct(
        public string $tweetId,
        public string $text,
        public int $estimatedTokens,
    ) {
        if ($this->tweetId === '') {
            throw new \InvalidArgumentException('tweetId must not be empty.');
        }

        if ($this->text === '') {
            throw new \InvalidArgumentException('text must not be empty.');
        }

        if ($this->estimatedTokens <= 0) {
            throw new \InvalidArgumentException('estimatedTokens must be greater than 0.');
        }
    }
}
