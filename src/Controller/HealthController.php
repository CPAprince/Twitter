<?php

declare(strict_types=1);

namespace Twitter\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class HealthController
{
    #[Route('/api/health', name: 'health', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return new JsonResponse(['status' => 'ok']);
    }
}
