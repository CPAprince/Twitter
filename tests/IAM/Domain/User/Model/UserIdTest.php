<?php

declare(strict_types=1);

namespace Twitter\Tests\IAM\Domain\User\Model;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Twitter\IAM\Domain\User\Model\UserId;

#[Group('unit')]
#[CoversClass(UserId::class)]
class UserIdTest extends TestCase
{
    #[Test]
    public function generateValidUserId(): void
    {
        $userId = UserId::generate();

        $this->assertTrue(Uuid::isValid((string) $userId));
    }

    #[Test]
    public function fromStringValidationPassed(): void
    {
        $userIdString = (string) UserId::generate();
        $userId = UserId::fromString($userIdString);

        $this->assertEquals($userIdString, (string) $userId);
    }

    #[Test]
    public function fromStringPassedInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        UserId::fromString('abracadabra');
    }
}
