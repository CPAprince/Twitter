<?php

declare(strict_types=1);

namespace Twitter\IAM\UI\REST\Request;

final class LogoutRequest
{
    public function __construct(
        private readonly string $refreshToken,
    ) {}

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }
}
