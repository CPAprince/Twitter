<?php

declare(strict_types=1);

namespace Twitter\Tweet\Application\UseCase\GetUserTweets;

use Twitter\Tweet\Application\UseCase\Shared\PaginationMeta;
use Twitter\Tweet\Domain\Tweet\Model\Tweet;

final readonly class GetUserTweetsQueryResult
{
    public function __construct(
        /** @var Tweet[] */
        public array $tweets,
        public PaginationMeta $meta,
    ) {}
}
