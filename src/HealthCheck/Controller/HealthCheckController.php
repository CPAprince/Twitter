<?php

namespace Twitter\HealthCheck\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Twitter\HealthCheck\Entity\HealthCheckReport;

#[Route('/api/health', name: 'api_health_check', methods: ['GET'])]
final class HealthCheckController
{
    public function __invoke(EntityManagerInterface $entityManager): JsonResponse
    {
        $report = new HealthCheckReport();
        $entityManager->persist($report);
        $entityManager->flush();

        return new JsonResponse($report);
    }
}
