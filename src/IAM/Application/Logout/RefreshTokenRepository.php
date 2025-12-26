<?php

declare(strict_types=1);

namespace Twitter\IAM\Application\Logout;

interface RefreshTokenRepository
{
    public function revoke(string $refreshToken, string $userId): void;

    public function deleteRevokedExpired(\DateTimeImmutable $now): int;
}
