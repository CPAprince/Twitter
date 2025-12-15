<?php

declare(strict_types=1);

namespace Twitter\Shared\Application\Bus\Query;

interface QueryHandlerInterface
{
    public function __invoke(QueryInterface $query): mixed;
}
