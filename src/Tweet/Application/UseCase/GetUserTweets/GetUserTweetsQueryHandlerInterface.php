<?php

declare(strict_types=1);

namespace Twitter\Tweet\Application\UseCase\GetUserTweets;

interface GetUserTweetsQueryHandlerInterface
{
    public function handle(GetUserTweetsQuery $query): GetUserTweetsQueryResult;
}
