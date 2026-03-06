<?php

declare(strict_types=1);

namespace Twitter\Tweet\Application\Moderation;

interface ModerationStatusUpdaterInterface
{
    /**
     * @param string[] $tweetIds
     */
    public function markApproved(array $tweetIds): void;

    /**
     * @param string[] $tweetIds
     */
    public function markRejected(array $tweetIds): void;
}
