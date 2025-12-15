<?php

declare(strict_types=1);

namespace Twitter\Health\UI\REST\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Twitter\Health\Application\PingDatabase\Query\PingDatabaseDTO;
use Twitter\Health\Application\PingDatabase\Query\PingDatabaseQuery;
use Twitter\Shared\Application\Bus\Query\QueryBusInterface;

#[AsController]
final readonly class HealthController
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $query = new PingDatabaseQuery();

        try {
            /* @var PingDatabaseDTO $pingResponse */
            $pingResponse = $this->queryBus->ask($query);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse($pingResponse);
    }
}
