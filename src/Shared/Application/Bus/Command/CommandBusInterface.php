<?php

declare(strict_types=1);

namespace Twitter\Shared\Application\Bus\Command;

interface CommandBusInterface
{
    /**
     * @throws \Throwable
     */
    public function dispatch(CommandInterface $command): void;
}
