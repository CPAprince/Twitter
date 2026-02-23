<?php

declare(strict_types=1);

namespace Twitter\Metrics\EventSubscriber;

use Prometheus\CollectorRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class HttpMetricsSubscriber implements EventSubscriberInterface
{
    private float $start;

    public function __construct(
        private readonly CollectorRegistry $registry,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 100],
            KernelEvents::RESPONSE => ['onResponse', -100],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $this->start = microtime(true);
    }

    public function onResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $duration = microtime(true) - $this->start;

        $request = $event->getRequest();
        $response = $event->getResponse();

        $route = $request->attributes->get('_route', 'unknown');
        $method = $request->getMethod();
        $status = (string) $response->getStatusCode();

        // total requests
        $requests = $this->registry->getOrRegisterCounter(
            'http',
            'requests_total',
            'Total HTTP requests',
            ['route', 'method', 'status']
        );

        $requests->inc([$route, $method, $status]);

        // latency histogram
        $latency = $this->registry->getOrRegisterHistogram(
            'http',
            'request_duration_seconds',
            'HTTP request latency',
            ['route'],
            [0.03, 0.05, 0.075, 0.1, 0.15, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1, 2, 3, 4, 5]
        );

        $latency->observe($duration, [$route]);

        // error counter
        if ($response->getStatusCode() >= 500) {
            $errors = $this->registry->getOrRegisterCounter(
                'http',
                'requests_errors_total',
                'HTTP 5xx errors',
                ['route']
            );

            $errors->inc([$route]);
        }
    }
}
