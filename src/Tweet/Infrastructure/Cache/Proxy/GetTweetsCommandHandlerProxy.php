<?php

declare(strict_types=1);

namespace Twitter\Tweet\Infrastructure\Cache\Proxy;

use Override;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Twitter\Tweet\Application\UseCase\GetTweets\GetTweetsCommand;
use Twitter\Tweet\Application\UseCase\GetTweets\GetTweetsCommandHandlerInterface;
use Twitter\Tweet\Application\UseCase\GetTweets\GetTweetsResponse;

final readonly class GetTweetsCommandHandlerProxy implements GetTweetsCommandHandlerInterface
{
    public function __construct(
        private GetTweetsCommandHandlerInterface $inner,
        private TagAwareCacheInterface $cacheTweets,
        private int $ttl,
    ) {}

    #[Override]
    public function handle(GetTweetsCommand $command): GetTweetsResponse
    {
        return $this->cacheTweets->get(
            sprintf(
                'tweets_main_limit_%d_page_%d',
                $command->limit,
                $command->page,
            ),
            function (ItemInterface $item) use ($command): GetTweetsResponse {
                $item->expiresAfter($this->ttl);
                $item->tag(['tweets_main']);

                return $this->inner->handle($command);
            }
        );
    }
}
