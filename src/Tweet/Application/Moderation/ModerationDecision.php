<?php

declare(strict_types=1);

namespace Twitter\Tweet\Application\Moderation;

use InvalidArgumentException;

final readonly class ModerationDecision
{
    public function __construct(
        public string $tweetId,
        public bool $approved,
        public ?string $reason = null,
        public array $categories = [],
    ) {
        if ('' === $this->tweetId) {
            throw new InvalidArgumentException('tweetId must not be empty.');
        }
    }

    public function isRejected(): bool
    {
        return !$this->approved;
    }
}
