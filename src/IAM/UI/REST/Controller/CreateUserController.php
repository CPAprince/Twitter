<?php

declare(strict_types=1);

namespace Twitter\IAM\UI\REST\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twitter\IAM\Application\CreateUser\CreateUserCommand;
use Twitter\IAM\Application\CreateUser\CreateUserCommandHandler;
use Twitter\IAM\Domain\User\Model\Exception\InvalidEmailException;
use Twitter\IAM\Domain\User\Model\Exception\InvalidPasswordException;
use Twitter\IAM\Domain\User\Model\Exception\UserAlreadyExistsException;

final class CreateUserController
{
    public function __construct(
        private CreateUserCommandHandler $createUserCommandHandler,
    ) {
    }

    /**
     * @throws InvalidEmailException
     * @throws InvalidPasswordException
     * @throws UserAlreadyExistsException
     */
    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), associative: true);

        if (!isset($data['email'], $data['password'])) {
            return new JsonResponse(['error' => 'Email and password are required'], Response::HTTP_BAD_REQUEST);
        }

        $command = new CreateUserCommand($data['email'], $data['password']);
        $result = $this->createUserCommandHandler->handle($command);

        return new JsonResponse(['id' => $result->userId], Response::HTTP_CREATED);
    }
}
