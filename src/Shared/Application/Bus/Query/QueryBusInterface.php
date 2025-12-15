<?php

declare(strict_types=1);

namespace Twitter\Shared\Application\Bus\Query;

interface QueryBusInterface
{
    /**
     * @throws \Throwable
     */
    public function ask(QueryInterface $query): mixed;
}
