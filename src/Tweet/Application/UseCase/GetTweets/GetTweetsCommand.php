<?php

declare(strict_types=1);

namespace Twitter\Tweet\Application\UseCase\GetTweets;

final readonly class GetTweetsCommand {

    public function __construct(
        public int $limit,
        public int $page,
    ){}
}

