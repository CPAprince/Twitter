<?php

declare(strict_types=1);

namespace Twitter\Tests\Domain\User\Model;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Twitter\IAM\Domain\User\Model\UserId;

#[Group('unit')]
class UserIdTest extends TestCase
{
    #[Test]
    public function generateValidUserId(): void
    {
        $userId = UserId::generate();

        $this->assertTrue(Uuid::isValid($userId->toString()));
    }

    #[Test]
    public function fromStringValidationPassed(): void
    {
        $userIdString = UserId::generate()->toString();
        $userId = UserId::fromString($userIdString);

        $this->assertEquals($userIdString, $userId->toString());
    }

    #[Test]
    public function fromStringPassedInvalidValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        UserId::fromString('abracadabra');
    }
}
