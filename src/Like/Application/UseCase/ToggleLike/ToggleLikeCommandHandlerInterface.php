<?php

declare(strict_types=1);

namespace Twitter\Like\Application\UseCase\ToggleLike;

use Twitter\Like\Domain\Like\Exception\LikeActionLockedException;
use Twitter\Like\Domain\Like\Exception\LikeAlreadyExistsException;

interface ToggleLikeCommandHandlerInterface
{
    /**
     * @throws LikeAlreadyExistsException
     * @throws LikeActionLockedException
     */
    public function handle(ToggleLikeCommand $command): ToggleLikeCommandResult;
}
