<?php

declare(strict_types=1);

namespace Twitter\IAM\Infrastructure\Http;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twitter\IAM\Domain\Auth\Exception\AuthTokenInvalidException;
use Twitter\IAM\Domain\Auth\Exception\AuthUnauthorizedException;
use Twitter\IAM\Domain\Auth\Exception\ValidationErrorException;

final class ExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onException'];
    }

    public function onException(ExceptionEvent $event): void
    {
        $path = $event->getRequest()->getPathInfo();
        if (!str_starts_with($path, '/api')) {
            return;
        }

        $e = $event->getThrowable();

        if ($e instanceof AuthUnauthorizedException) {
            $event->setResponse(new JsonResponse(['error' => AuthUnauthorizedException::ERROR_CODE], 401));

            return;
        }

        if ($e instanceof AuthTokenInvalidException) {
            $event->setResponse(new JsonResponse(['error' => AuthTokenInvalidException::ERROR_CODE], 401));

            return;
        }

        if ($e instanceof ValidationErrorException) {
            $event->setResponse(new JsonResponse([
                'error' => ValidationErrorException::ERROR_CODE,
                'message' => $e->getMessage(),
            ], 422));

            return;
        }

        $event->setResponse(new JsonResponse(['error' => 'INTERNAL_ERROR'], 500));
    }
}
