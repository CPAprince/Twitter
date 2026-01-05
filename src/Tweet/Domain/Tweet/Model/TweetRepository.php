<?php

declare(strict_types=1);

namespace Twitter\Tweet\Domain\Tweet\Model;

use Twitter\Tweet\Domain\Tweet\Exception\TweetNotFoundException;

interface TweetRepository
{
    public function add(Tweet $tweet): void;

    public function save(Tweet $tweet): void;

    /**
     * @throws TweetNotFoundException
     */
    public function getById(string $tweetId): Tweet;
}
