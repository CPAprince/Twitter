<?php

declare(strict_types=1);

namespace Twitter\Metrics\Controller;

use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class MetricsController
{
    #[Route('/metrics', name: 'metrics', methods: ['GET'])]
    public function __invoke(CollectorRegistry $registry): Response
    {

        $renderer = new RenderTextFormat();
        $metrics = $registry->getMetricFamilySamples();

        return new Response(
            $renderer->render($metrics),
            200,
            ['Content-Type' => RenderTextFormat::MIME_TYPE]
        );
    }
}
