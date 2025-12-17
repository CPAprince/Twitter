<?php

declare(strict_types=1);

namespace Twitter\IAM\Application\Logout;

interface RefreshTokenStoreInterface
{
    public function revoke(string $refreshToken, string $userId): void;
}
