<?php

declare(strict_types=1);

namespace Twitter\IAM\Application\Logout;

final class LogoutService
{
    public function __construct(private readonly RefreshTokenStoreInterface $store)
    {
    }

    public function logout(string $userId, string $refreshToken): void
    {
        $this->store->revoke($refreshToken, $userId);
    }
}
