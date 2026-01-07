<?php

declare(strict_types=1);

namespace Twitter\Profile\UI\REST\Controller\UpdateProfile;

use Assert\Assert;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Twitter\IAM\Domain\User\Model\User;
use Twitter\Profile\Application\UseCase\UpdateProfile\UpdateProfileCommand;
use Twitter\Profile\Application\UseCase\UpdateProfile\UpdateProfileCommandHandler;
use Twitter\Profile\Domain\Profile\Exception\ProfileAccessDeniedException;
use Twitter\Profile\Domain\Profile\Exception\ProfileNotFoundException;

#[Route('/api/profiles/{userId}', name: 'api_update_profile', methods: [Request::METHOD_PUT])]
final readonly class UpdateProfileController
{
    public function __construct(private UpdateProfileCommandHandler $commandHandler) {}

    /**
     * @throws ProfileNotFoundException
     * @throws ProfileAccessDeniedException
     */
    public function __invoke(
        string $userId,
        #[MapRequestPayload] UpdateProfileRequest $request,
        #[CurrentUser] User $authUser,
    ): JsonResponse {
        Assert::lazy()->tryAll()
            ->that($userId, 'userId')->uuid()
            ->that($authUser->id(), 'authUserId')->same($userId)
            ->verifyNow();

        $command = new UpdateProfileCommand($userId, $request->name(), $request->bio());
        $result = $this->commandHandler->handle($command);

        return new JsonResponse([
            'name' => $result->name,
            'bio' => $result->bio,
            'updatedAt' => $result->updatedAt->format(DATE_RFC3339),
        ], Response::HTTP_OK);
    }
}
