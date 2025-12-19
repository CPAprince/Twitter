<?php

declare(strict_types=1);

namespace Twitter\IAM\Application\CreateUser;

use RuntimeException;
use Throwable;
use Twitter\IAM\Domain\User\Model\Email;
use Twitter\IAM\Domain\User\Model\Exception\InvalidEmailException;
use Twitter\IAM\Domain\User\Model\Exception\InvalidPasswordException;
use Twitter\IAM\Domain\User\Model\Exception\UserAlreadyExistsException;
use Twitter\IAM\Domain\User\Model\PasswordHash;
use Twitter\IAM\Domain\User\Model\User;
use Twitter\IAM\Domain\User\Model\UserRepository;

final readonly class CreateUserCommandHandler
{
    public function __construct(
        private UserRepository $userRepository,
    ) {}

    /**
     * @throws InvalidEmailException
     * @throws InvalidPasswordException
     * @throws UserAlreadyExistsException
     */
    public function handle(CreateUserCommand $command): CreateUserCommandResult
    {
        $email = Email::fromString($command->email);

        if ($this->userRepository->existsByEmail($email->toString())) {
            throw new UserAlreadyExistsException($email->toString());
        }

        $passwordHash = PasswordHash::fromPlainPassword($command->password);
        $user = User::create($email, $passwordHash);

        try {
            $this->userRepository->add($user);
        } catch (Throwable $throwable) {
            throw new RuntimeException('Unexpected error', previous: $throwable);
        }

        return new CreateUserCommandResult($user->getId());
    }
}
