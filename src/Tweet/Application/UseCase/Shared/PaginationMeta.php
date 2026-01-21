<?php

declare(strict_types=1);

namespace Twitter\Tweet\Application\UseCase\Shared;

use Assert\Assert;

final readonly class PaginationMeta
{
    public int $totalPages;

    public function __construct(
        public int $page,
        public int $limit,
        public int $totalItems,
    )
    {
        Assert::lazy()
            ->that($page, 'page')->min(1)
            ->that($limit, 'limit')->min(1)
            ->that($totalItems, 'totalItems')->min(0)
            ->verifyNow();

        $this->totalPages = (int)ceil($totalItems / $limit);
    }
}
