<?php

declare(strict_types=1);

namespace Twitter\Tests\IAM\Application\Logout;

use PHPUnit\Framework\TestCase;
use Twitter\IAM\Application\Logout\LogoutService;
use Twitter\IAM\Application\Logout\RefreshTokenStoreInterface;

final class LogoutServiceTest extends TestCase
{
    public function testLogoutCallsStoreRevoke(): void
    {
        $store = $this->createMock(RefreshTokenStoreInterface::class);

        $userId = '019b2bd9-f57c-7088-824e-b6f96f27a1ba';
        $refreshToken = 'some-refresh-token';

        $store->expects(self::once())
            ->method('revoke')
            ->with($refreshToken, $userId);

        $service = new LogoutService($store);
        $service->logout($userId, $refreshToken);
    }
}
