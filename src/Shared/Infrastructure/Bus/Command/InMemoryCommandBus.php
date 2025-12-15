<?php

declare(strict_types=1);

namespace Twitter\Shared\Infrastructure\Bus\Command;

use Symfony\Component\DependencyInjection\ServiceLocator;
use Twitter\Shared\Application\Bus\Command\CommandBusInterface;
use Twitter\Shared\Application\Bus\Command\CommandHandlerInterface;
use Twitter\Shared\Application\Bus\Command\CommandInterface;

final readonly class InMemoryCommandBus implements CommandBusInterface
{
    public function __construct(
        private ServiceLocator $handlersLocator,
    ) {
    }

    #[\Override]
    public function dispatch(CommandInterface $command): void
    {
        $commandClass = $command::class.'Handler';

        if (!$this->handlersLocator->has($commandClass)) {
            throw new \RuntimeException(sprintf('No handler found for command "%s"', $commandClass));
        }

        /** @var CommandHandlerInterface $handler */
        $handler = $this->handlersLocator->get($commandClass);
        $handler($command);
    }
}
