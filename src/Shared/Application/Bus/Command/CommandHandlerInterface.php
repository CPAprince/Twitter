<?php

declare(strict_types=1);

namespace Twitter\Shared\Application\Bus\Command;

interface CommandHandlerInterface
{
    public function __invoke(CommandInterface $command): void;
}
