<?php

declare(strict_types=1);

namespace Twitter\Profile\Application\UseCase\UpdateProfile;

use Twitter\Profile\Domain\Profile\Exception\ProfileAccessDeniedException;
use Twitter\Profile\Domain\Profile\Exception\ProfileNotFoundException;
use Twitter\Profile\Domain\Profile\Model\ProfileRepository;

final readonly class UpdateProfileCommandHandler
{
    public function __construct(private ProfileRepository $profileRepository) {}

    /**
     * @throws ProfileNotFoundException
     * @throws ProfileAccessDeniedException
     */
    public function handle(UpdateProfileCommand $command): UpdateProfileCommandResult
    {
        $profile = $this->profileRepository->getByUserId($command->userId);

        if ($profile->userId() !== $command->userId) {
            throw new ProfileAccessDeniedException($command->userId);
        }

        $profile->changeName($command->name);
        $profile->changeBio($command->bio);

        $this->profileRepository->flush();

        return new UpdateProfileCommandResult(
            $profile->name(),
            $profile->bio(),
            $profile->updatedAt(),
        );
    }
}
