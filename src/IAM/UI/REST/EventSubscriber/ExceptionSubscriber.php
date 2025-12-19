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
use Throwable;
use Twitter\IAM\Domain\User\Model\Exception\InvalidEmailException;
use Twitter\IAM\Domain\User\Model\Exception\InvalidPasswordException;
use Twitter\IAM\Domain\User\Model\Exception\UserAlreadyExistsException;

final readonly class ExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

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

        $this->logger->error('Exception caught', [
            'exception' => $throwable::class,
            'message' => $throwable->getMessage(),
            'trace' => $throwable->getTraceAsString(),
        ]);

        $response = $this->createErrorResponse($throwable);
        $event->setResponse($response);
    }

    private function createErrorResponse(Throwable $throwable): JsonResponse
    {
        $class = $throwable::class;
        if (array_key_exists($class, self::EXCEPTION_MAPPING)) {
            $config = self::EXCEPTION_MAPPING[$class];

            return new JsonResponse([
                'error' => [
                    'code' => $config['code'],
                    'message' => $config['message'],
                ],
            ], $config['status']);
        }

        if ($throwable instanceof HttpExceptionInterface) {
            return new JsonResponse([
                'error' => [
                    'code' => 'HTTP_ERROR',
                    'message' => 'An error occurred',
                ],
            ], $throwable->getStatusCode());
        }

        return new JsonResponse(
            [
                'error' => [
                    'code' => 'INTERNAL_SERVER_ERROR',
                    'message' => 'An unexpected error occurred. Please try again later',
                ],
            ],
            Response::HTTP_INTERNAL_SERVER_ERROR,
        );
    }
}
