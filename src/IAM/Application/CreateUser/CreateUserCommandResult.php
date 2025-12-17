<?php

declare(strict_types=1);

namespace Twitter\IAM\Application\CreateUser;

use Twitter\IAM\Domain\User\Model\UserId;

final readonly class CreateUserCommandResult
{
    public function __construct(
        public UserId $userId,
    ) {
    }
}
