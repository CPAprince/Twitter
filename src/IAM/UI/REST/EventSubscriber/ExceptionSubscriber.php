<?php

declare(strict_types=1);

namespace Twitter\IAM\UI\REST\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Twitter\IAM\Domain\Auth\Exception\BadRequestException;
use Twitter\IAM\Domain\Auth\Exception\TokenInvalidException;
use Twitter\IAM\Domain\Auth\Exception\UnauthorizedException;
use Twitter\IAM\Domain\Auth\Exception\ValidationErrorException;

final readonly class ExceptionSubscriber implements EventSubscriberInterface
{
    private const string API_PREFIX = '/api';

    private const string CODE_INTERNAL_SERVER_ERROR = 'INTERNAL_SERVER_ERROR';
    private const string CODE_HTTP_ERROR = 'HTTP_ERROR';

    private const array EXCEPTION_MAPPING = [
        BadRequestException::class => [
            'code' => BadRequestException::ERROR_CODE,
            'message' => 'Bad request',
            'status' => Response::HTTP_BAD_REQUEST,
        ],

        UnauthorizedException::class => [
            'code' => UnauthorizedException::ERROR_CODE,
            'message' => 'Unauthorized',
            'status' => Response::HTTP_UNAUTHORIZED,
        ],

        TokenInvalidException::class => [
            'code' => TokenInvalidException::ERROR_CODE,
            'message' => 'Invalid or expired token',
            'status' => Response::HTTP_UNAUTHORIZED,
        ],

        ValidationErrorException::class => [
            'code' => ValidationErrorException::ERROR_CODE,
            'message' => 'Validation failed',
            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
        ],
    ];

    public function __construct(
        private LoggerInterface $logger,
        private string $environment = 'prod',
    ) {
    }

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

        $path = $event->getRequest()->getPathInfo();
        if (!str_starts_with($path, self::API_PREFIX)) {
            return;
        }

        $throwable = $event->getThrowable();

        $this->logException($throwable);

        $event->setResponse($this->createErrorResponse($throwable));
    }

    private function logException(\Throwable $throwable): void
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

    private function createErrorResponse(\Throwable $throwable): JsonResponse
    {
        $class = $throwable::class;

        if (isset(self::EXCEPTION_MAPPING[$class])) {
            $config = self::EXCEPTION_MAPPING[$class];

            return new JsonResponse([
                'error' => [
                    'code' => (string) $config['code'],
                    'message' => (string) $config['message'],
                ],
            ], (int) $config['status']);
        }

        if ($throwable instanceof HttpExceptionInterface) {
            return new JsonResponse([
                'error' => [
                    'code' => self::CODE_HTTP_ERROR,
                    'message' => 'An error occurred',
                ],
            ], $throwable->getStatusCode());
        }

        return new JsonResponse([
            'error' => [
                'code' => self::CODE_INTERNAL_SERVER_ERROR,
                'message' => 'An unexpected error occurred. Please try again later',
            ],
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
