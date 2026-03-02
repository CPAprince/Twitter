<?php

declare(strict_types=1);

namespace Twitter\Tweet\Application\UseCase\UpdateTweet;

use Twitter\Tweet\Domain\Tweet\Exception\TweetAccessDeniedException;
use Twitter\Tweet\Domain\Tweet\Exception\TweetNotFoundException;

interface UpdateTweetCommandHandlerInterface
{
    /**
     * @throws TweetAccessDeniedException
     * @throws TweetNotFoundException
     */
    public function handle(UpdateTweetCommand $command): UpdateTweetCommandResult;
}
