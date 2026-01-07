<?php

declare(strict_types=1);

namespace Twitter\Tweet\Domain\Tweet\Model;

interface TweetRepository
{
    public function add(Tweet $tweet): void;
}
