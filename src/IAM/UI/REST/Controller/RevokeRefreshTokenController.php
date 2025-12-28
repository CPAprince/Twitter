<?php

declare(strict_types=1);

namespace Twitter\IAM\UI\REST\Controller;

use JsonException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twitter\IAM\Application\Logout\LogoutCommand;
use Twitter\IAM\Application\Logout\LogoutHandler;
use Twitter\IAM\Domain\Auth\Exception\BadRequestException;
use Twitter\IAM\Domain\Auth\Exception\UnauthorizedException;
use Twitter\IAM\Infrastructure\Security\UserIdAwareInterface;
use Twitter\IAM\UI\REST\Request\LogoutRequest;

final readonly class RevokeRefreshTokenController
{
    public function __construct(
        private Security $security,
        private LogoutHandler $logoutHandler,
    ) {}

    #[Route('/api/tokens', name: 'api_tokens_logout', methods: ['DELETE'])]
    public function logout(Request $request): Response
    {
        $payload = $this->decodeJson($request);

        $logoutRequest = new LogoutRequest(
            refreshToken: (string) ($payload['refreshToken'] ?? ''),
        );

        $user = $this->security->getUser();
        if (!$user instanceof UserIdAwareInterface) {
            throw new UnauthorizedException();
        }

        ($this->logoutHandler)(new LogoutCommand(
            userId: $user->getId(),
            refreshToken: $logoutRequest->getRefreshToken(),
        ));

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    private function decodeJson(Request $request): array
    {
        try {
            return json_decode(
                json: $request->getContent(),
                associative: true,
                flags: JSON_THROW_ON_ERROR,
            );
        } catch (JsonException) {
            throw new BadRequestException('Invalid JSON.');
        }
    }
}
