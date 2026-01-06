<?php

declare(strict_types=1);

namespace Twitter\IAM\Application\Logout;

final readonly class LogoutCommand
{
    public function __construct(
        private string $userId,
        private string $refreshToken,
    ) {}

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function refreshToken(): string
    {
        return $this->refreshToken;
    }
}
