<?php

declare(strict_types=1);

namespace Twitter\Tweet\Domain\Tweet\Exception;

use Exception;

final class TweetNotFoundException extends Exception
{
    public function __construct(string $tweetId)
    {
        parent::__construct('Tweet with id "'.$tweetId.'" not found');
    }
}
