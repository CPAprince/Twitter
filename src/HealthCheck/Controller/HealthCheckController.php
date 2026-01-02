<?php

declare(strict_types=1);

namespace Twitter\HealthCheck\Controller;

use DateTimeImmutable;
use DateTimeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/health', name: 'api_health_check', methods: ['GET'])]
final class HealthCheckController
{
    public function __invoke(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'ok',
            'checkedAt' => new DateTimeImmutable()->format(DateTimeInterface::RFC3339),
            'RANDOM' => rand(1000000000, 9999999999),
            'UUIDrandom' => 'RAND: '.uuid_create(),
        ]);
    }
}
