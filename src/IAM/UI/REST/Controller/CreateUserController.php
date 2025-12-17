<?php

declare(strict_types=1);

namespace Twitter\IAM\UI\REST\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twitter\IAM\Domain\User\Model\Email;
use Twitter\IAM\Domain\User\Model\Exception\InvalidEmailException;
use Twitter\IAM\Domain\User\Model\Exception\InvalidPasswordException;
use Twitter\IAM\Domain\User\Model\PasswordHash;
use Twitter\IAM\Domain\User\Model\User;
use Twitter\IAM\Domain\User\Model\UserRepository;

final class CreateUserController
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), associative: true);

        if (!isset($data['email'], $data['password'])) {
            return new JsonResponse(['error' => 'Email and password are required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $email = Email::fromString($data['email']);

            if (null !== $this->userRepository->findByEmail($email)) {
                return new JsonResponse(['error' => 'User with this email already exists'], Response::HTTP_CONFLICT);
            }

            $passwordHash = PasswordHash::fromPlainPassword($data['password']);
            $user = User::create($email, $passwordHash);

            $this->userRepository->save($user);

            return new JsonResponse([
                'id' => $user->getId()->toString(),
            ], Response::HTTP_CREATED);
        } catch (InvalidEmailException $emailException) {
            return new JsonResponse([
                'error' => 'Invalid email',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (InvalidPasswordException $passwordException) {
            return new JsonResponse([
                'error' => 'Invalid password',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $throwable) {
            return new JsonResponse(['error' => 'Unexpected error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
