<?php

declare(strict_types=1);

namespace Twitter\IAM\UI\REST\Request;

use Twitter\IAM\Domain\Auth\Exception\ValidationErrorException;

final readonly class LogoutRequest
{
    public function __construct(
        private string $refreshToken,
    ) {
        if ('' === trim($this->refreshToken)) {
            throw new ValidationErrorException('refreshToken is required.');
        }
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }
}
