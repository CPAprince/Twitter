<?php

declare(strict_types=1);

namespace Twitter\IAM\Application\Logout;

final class LogoutHandler
{
    public function __construct(private readonly RefreshTokenRepository $store)
    {
    }

    public function __invoke(LogoutCommand $command): void
    {
        $this->store->revoke($command->refreshToken, $command->userId);
    }
}
