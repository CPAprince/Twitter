<?php

declare(strict_types=1);

namespace Twitter\IAM\UI\REST\Controller;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twitter\IAM\Application\Logout\LogoutCommand;
use Twitter\IAM\Application\Logout\LogoutHandler;
use Twitter\IAM\Domain\Auth\Exception\UnauthorizedException;
use Twitter\IAM\Infrastructure\Security\UserIdAwareInterface;
use Twitter\IAM\UI\REST\Request\LogoutRequestMapper;
use Twitter\IAM\UI\REST\Validation\LogoutRequestValidator;

final readonly class RevokeRefreshTokenController
{
    public function __construct(
        private Security $security,
        private LogoutHandler $logoutHandler,
        private LogoutRequestMapper $requestMapper,
        private LogoutRequestValidator $validator,
    ) {
    }

    #[Route('/api/tokens', name: 'api_tokens_logout', methods: ['DELETE'])]
    public function logout(Request $request): Response
    {
        $logoutRequest = $this->requestMapper->fromHttp($request);
        $this->validator->validate($logoutRequest);

        $user = $this->security->getUser();
        if (!$user instanceof UserIdAwareInterface) {
            throw new UnauthorizedException();
        }

        ($this->logoutHandler)(new LogoutCommand(
            userId: $user->getId(),
            refreshToken: $logoutRequest->refreshToken,
        ));

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
