<?php

declare(strict_types=1);

namespace Twitter\IAM\UI\REST\EventSubscriber;

use Assert\InvalidArgumentException;
use Assert\LazyAssertionException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;
use Twitter\IAM\Domain\User\Exception\InvalidEmailException;
use Twitter\IAM\Domain\User\Exception\InvalidPasswordException;
use Twitter\IAM\Domain\User\Exception\UserAlreadyExistsException;

final readonly class ExceptionSubscriber implements EventSubscriberInterface
{
    private const array EXCEPTION_MAPPING = [
        InvalidEmailException::class => [
            'code' => 'INVALID_EMAIL',
            'message' => 'The provided email address is invalid',
            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
        ],
        InvalidPasswordException::class => [
            'code' => 'INVALID_PASSWORD',
            'message' => 'The provided password does not meet requirements',
            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
        ],
        UserAlreadyExistsException::class => [
            'code' => 'USER_ALREADY_EXISTS',
            'message' => 'A user with this email already exists',
            'status' => Response::HTTP_CONFLICT,
        ],
    ];

    public function __construct(
        private LoggerInterface $logger,
        private string $environment = 'prod',
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $throwable = $event->getThrowable();

        $this->logException($throwable);

        $response = $this->createErrorResponse($throwable);
        $event->setResponse($response);
    }

    private function logException(Throwable $throwable): void
    {
        $context = [
            'exception' => $throwable::class,
            'message' => $throwable->getMessage(),
        ];

        if ('dev' === $this->environment) {
            $context['trace'] = $throwable->getTraceAsString();
        }

        $this->logger->error('Exception caught', $context);
    }

    private function createErrorResponse(Throwable $throwable): JsonResponse
    {
        $class = $throwable::class;
        $code = 'INTERNAL_SERVER_ERROR';
        $message = 'An unexpected error occurred. Please try again later';
        $status = Response::HTTP_INTERNAL_SERVER_ERROR;

        if ($response = $this->validationErrorResponse($throwable)) {
            return $response;
        }

        if (array_key_exists($class, self::EXCEPTION_MAPPING)) {
            $config = self::EXCEPTION_MAPPING[$class];
            $code = $config['code'];
            $message = $config['message'];
            $status = $config['status'];
        } elseif ($throwable instanceof HttpExceptionInterface) {
            $code = 'HTTP_ERROR';
            $message = 'An error occurred';
            $status = $throwable->getStatusCode();
        }

        return new JsonResponse([
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ], $status);
    }

    private function validationErrorResponse(Throwable $throwable): ?JsonResponse
    {
        if (!$throwable instanceof LazyAssertionException) {
            return null;
        }

        return new JsonResponse(
            ['errors' => $this->mapLazyAssertionErrors($throwable)],
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    private function mapLazyAssertionErrors(LazyAssertionException $exception): array
    {
        return array_map(
            static fn (InvalidArgumentException $error): array => [
                'field' => $error->getPropertyPath(),
                'message' => $error->getMessage(),
            ],
            $exception->getErrorExceptions()
        );
    }
}
