<?php

declare(strict_types=1);

namespace Twitter\IAM\Infrastructure\Persistence\Auth;

final class RefreshTokenHasher
{
    public function __construct(private readonly string $secret)
    {
    }

    public function hash(string $refreshToken): string
    {
        return hash_hmac('sha256', $refreshToken, $this->secret, true);
    }
}
