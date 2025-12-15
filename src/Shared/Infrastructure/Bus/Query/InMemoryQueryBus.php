<?php

declare(strict_types=1);

namespace Twitter\Shared\Infrastructure\Bus\Query;

use Symfony\Component\DependencyInjection\ServiceLocator;
use Twitter\Shared\Application\Bus\Query\QueryBusInterface;
use Twitter\Shared\Application\Bus\Query\QueryHandlerInterface;
use Twitter\Shared\Application\Bus\Query\QueryInterface;

final readonly class InMemoryQueryBus implements QueryBusInterface
{
    public function __construct(
        private ServiceLocator $handlersLocator,
    ) {
    }

    #[\Override]
    public function ask(QueryInterface $query): mixed
    {
        $queryClass = $query::class.'Handler';

        if (!$this->handlersLocator->has($queryClass)) {
            throw new \RuntimeException(sprintf('No handler found for query "%s"', $queryClass));
        }

        /** @var QueryHandlerInterface $handler */
        $handler = $this->handlersLocator->get($queryClass);

        return $handler($query);
    }
}
