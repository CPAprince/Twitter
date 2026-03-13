<?php

declare(strict_types=1);

namespace Twitter\Profile\Infrastructure\Cache\Proxy;

use Override;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Twitter\Profile\Application\UseCase\GetProfile\GetProfileQuery;
use Twitter\Profile\Application\UseCase\GetProfile\GetProfileQueryHandlerInterface;
use Twitter\Profile\Application\UseCase\GetProfile\GetProfileQueryResult;

final readonly class GetProfileQueryHandlerProxy implements GetProfileQueryHandlerInterface
{
    public function __construct(
        private GetProfileQueryHandlerInterface $inner,
        private TagAwareCacheInterface $cacheProfiles,
        private int $ttl,
    ) {}

    #[Override]
    public function handle(GetProfileQuery $query): GetProfileQueryResult
    {
        $cacheKey = sprintf('profile_%s', $query->userId);

        return $this->cacheProfiles->get(
            $cacheKey,
            function (ItemInterface $item) use ($query, $cacheKey): GetProfileQueryResult {
                $item->expiresAfter($this->ttl);
                $item->tag(['profile', $cacheKey]);

                return $this->inner->handle($query);
            },
        );
    }
}
