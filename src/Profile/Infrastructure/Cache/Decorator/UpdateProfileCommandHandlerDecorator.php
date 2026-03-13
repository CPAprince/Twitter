<?php

declare(strict_types=1);

namespace Twitter\Profile\Infrastructure\Cache\Decorator;

use Override;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Twitter\IAM\Domain\Auth\Exception\UnauthorizedException;
use Twitter\Profile\Application\UseCase\UpdateProfile\UpdateProfileCommand;
use Twitter\Profile\Application\UseCase\UpdateProfile\UpdateProfileCommandHandlerInterface;
use Twitter\Profile\Application\UseCase\UpdateProfile\UpdateProfileCommandResult;
use Twitter\Profile\Domain\Profile\Exception\ProfileNotFoundException;

final readonly class UpdateProfileCommandHandlerDecorator implements UpdateProfileCommandHandlerInterface
{
    public function __construct(
        private UpdateProfileCommandHandlerInterface $inner,
        private TagAwareCacheInterface $cacheProfiles,
    ) {}

    /**
     * @throws ProfileNotFoundException
     * @throws UnauthorizedException
     */
    #[Override]
    public function handle(UpdateProfileCommand $command): UpdateProfileCommandResult
    {
        $result = $this->inner->handle($command);

        $this->cacheProfiles->invalidateTags([
            sprintf(
                'profile_%s',
                $command->userId,
            ),
        ]);

        return $result;
    }
}
