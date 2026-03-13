<?php

declare(strict_types=1);

namespace Twitter\Tweet\Infrastructure\Cache\Proxy;

use Override;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Twitter\Tweet\Application\UseCase\GetUserTweets\GetUserTweetsQuery;
use Twitter\Tweet\Application\UseCase\GetUserTweets\GetUserTweetsQueryHandlerInterface;
use Twitter\Tweet\Application\UseCase\GetUserTweets\GetUserTweetsQueryResult;

final readonly class GetUserTweetsQueryHandlerProxy implements GetUserTweetsQueryHandlerInterface
{
    public function __construct(
        private GetUserTweetsQueryHandlerInterface $inner,
        private TagAwareCacheInterface $cacheTweets,
        private int $ttl,
    ) {}

    #[Override]
    public function handle(GetUserTweetsQuery $query): GetUserTweetsQueryResult
    {
        return $this->cacheTweets->get(
            sprintf(
                'user_tweets_%s_limit_%d_page_%d',
                $query->userId,
                $query->limit,
                $query->page,
            ),
            function (ItemInterface $item) use ($query): GetUserTweetsQueryResult {
                $item->expiresAfter($this->ttl);
                $item->tag([
                    'user_tweets',
                    sprintf('user_tweets_%s', $query->userId),
                ]);

                return $this->inner->handle($query);
            }
        );
    }
}
