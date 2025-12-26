<?php

declare(strict_types=1);

namespace Twitter\IAM\Application\Logout;

final readonly class LogoutCommand
{
    public function __construct(
        public string $userId,
        public string $refreshToken,
    ) {
    }
}
