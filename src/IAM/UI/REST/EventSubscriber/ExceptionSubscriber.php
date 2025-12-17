<?php

declare(strict_types=1);

namespace Twitter\IAM\UI\REST\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Twitter\IAM\Domain\User\Model\Exception\InvalidEmailException;
use Twitter\IAM\Domain\User\Model\Exception\InvalidPasswordException;
use Twitter\IAM\Domain\User\Model\Exception\UserAlreadyExistsException;

final readonly class ExceptionSubscriber implements EventSubscriberInterface
{
    private const array EXCEPTION_HTTP_CODE_MAP = [
        InvalidEmailException::class => Response::HTTP_UNPROCESSABLE_ENTITY,
        InvalidPasswordException::class => Response::HTTP_UNPROCESSABLE_ENTITY,
        UserAlreadyExistsException::class => Response::HTTP_CONFLICT,
    ];

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 0],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $throwable = $event->getThrowable();
        $response = new JsonResponse([
            'error' => $throwable->getMessage(),
        ], $this->httpCodeFromException($throwable));
        $event->setResponse($response);
    }

    private function httpCodeFromException(\Throwable $throwable): int
    {
        $class = $throwable::class;
        if (array_key_exists($class, self::EXCEPTION_HTTP_CODE_MAP)) {
            return self::EXCEPTION_HTTP_CODE_MAP[$class];
        }

        if ($throwable instanceof HttpExceptionInterface) {
            return $throwable->getStatusCode();
        }

        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
