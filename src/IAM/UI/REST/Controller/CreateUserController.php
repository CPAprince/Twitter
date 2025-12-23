<?php

declare(strict_types=1);

namespace Twitter\IAM\UI\REST\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Twitter\IAM\Application\CreateUser\CreateUserCommand;
use Twitter\IAM\Application\CreateUser\CreateUserCommandHandler;
use Twitter\IAM\Domain\User\Model\Exception\InvalidEmailException;
use Twitter\IAM\Domain\User\Model\Exception\InvalidPasswordException;
use Twitter\IAM\Domain\User\Model\Exception\UserAlreadyExistsException;

final readonly class CreateUserController
{
    public function __construct(
        private CreateUserCommandHandler $createUserCommandHandler,
    ) {}

    /**
     * @throws InvalidEmailException
     * @throws InvalidPasswordException
     * @throws UserAlreadyExistsException
     */
    public function __invoke(
        #[MapRequestPayload]
        CreateUserRequest $request,
    ): JsonResponse {
        $command = new CreateUserCommand($request->email(), $request->password());
        $result = $this->createUserCommandHandler->handle($command);

        return new JsonResponse(['id' => $result->userId], Response::HTTP_CREATED);
    }
}
