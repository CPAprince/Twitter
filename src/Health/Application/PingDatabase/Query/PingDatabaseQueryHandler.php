<?php

declare(strict_types=1);

namespace Twitter\Health\Application\PingDatabase\Query;

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Twitter\Shared\Application\Bus\Query\QueryHandlerInterface;
use Twitter\Shared\Application\Bus\Query\QueryInterface;

final readonly class PingDatabaseQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(QueryInterface|PingDatabaseQuery $query): PingDatabaseDTO
    {
        try {
            $connection = $this->entityManager->getConnection();
            $connection->fetchOne('SELECT 1');
        } catch (DBALException $e) {
            return new PingDatabaseDTO('error', $e->getMessage());
        }

        return new PingDatabaseDTO('connected', 'Database is up and running');
    }
}
