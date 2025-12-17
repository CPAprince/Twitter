<?php

declare(strict_types=1);

namespace Twitter\IAM\UI\REST\Controller;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twitter\IAM\Application\Logout\LogoutService;
use Twitter\IAM\Domain\Auth\Exception\AuthUnauthorizedException;
use Twitter\IAM\Domain\Auth\Exception\ValidationErrorException;
use Twitter\IAM\Infrastructure\Security\UserIdAwareInterface;

final class TokenController
{
    public function __construct(
        private readonly Security $security,
        private readonly LogoutService $logoutService,
    ) {
    }

    #[Route('/api/token', name: 'api_token_logout', methods: ['DELETE'])]
    public function logout(Request $request): Response
    {
        $user = $this->security->getUser();
        if (null === $user) {
            throw new AuthUnauthorizedException();
        }

        if (!$user instanceof UserIdAwareInterface) {
            throw new \RuntimeException('Authenticated user must implement UserIdAwareInterface (getId()).');
        }

        if ('json' !== $request->getContentTypeFormat()) {
            throw new ValidationErrorException('Content-Type must be application/json.');
        }

        try {
            $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new ValidationErrorException('Invalid JSON.');
        }

        if (!is_array($payload)) {
            throw new ValidationErrorException('JSON body must be an object.');
        }

        $refreshToken = $payload['refreshToken'] ?? null;
        if (!is_string($refreshToken) || '' === $refreshToken) {
            throw new ValidationErrorException('refreshToken is required.');
        }

        $this->logoutService->logout($user->getId(), $refreshToken);

        return new Response(null, 204);
    }
}
