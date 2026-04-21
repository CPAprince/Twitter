<?php

declare(strict_types=1);

namespace Twitter\Tweet\Application\UseCase\GetTweets;

interface GetTweetsCommandHandlerInterface
{
    public function handle(GetTweetsCommand $command): GetTweetsResponse;
}
