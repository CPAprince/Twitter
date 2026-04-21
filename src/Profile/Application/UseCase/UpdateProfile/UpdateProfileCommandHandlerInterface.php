<?php

declare(strict_types=1);

namespace Twitter\Profile\Application\UseCase\UpdateProfile;

use Twitter\IAM\Domain\Auth\Exception\UnauthorizedException;
use Twitter\Profile\Domain\Profile\Exception\ProfileNotFoundException;

interface UpdateProfileCommandHandlerInterface
{
    /**
     * @throws ProfileNotFoundException
     * @throws UnauthorizedException
     */
    public function handle(UpdateProfileCommand $command): UpdateProfileCommandResult;
}
