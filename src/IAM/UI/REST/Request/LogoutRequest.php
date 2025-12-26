<?php

declare(strict_types=1);

namespace Twitter\IAM\UI\REST\Request;

final class LogoutRequest
{
    public function __construct(
        public readonly string $refreshToken,
    ) {
    }
}
