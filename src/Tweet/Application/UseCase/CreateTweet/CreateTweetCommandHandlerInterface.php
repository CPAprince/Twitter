<?php

declare(strict_types=1);

namespace Twitter\Tweet\Application\UseCase\CreateTweet;

interface CreateTweetCommandHandlerInterface
{
    public function handle(CreateTweetCommand $command): CreateTweetCommandResult;
}
