<?php

declare(strict_types=1);

namespace Twitter\Tests\IAM\Domain\User\Model;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twitter\IAM\Domain\User\Model\UserId;

#[Group('unit')]
#[CoversClass(UserId::class)]
class UserIdTest extends TestCase
{
    #[Test]
    public function generatesANonEmptyIdentifier(): void
    {
        $userId = UserId::generate();

        self::assertNotEmpty((string) $userId);
    }

    #[Test]
    public function canBeRecreatedFromItsStringRepresentation(): void
    {
        $original = UserId::generate();
        $restored = UserId::fromString((string) $original);

        self::assertSame((string) $original, (string) $restored);
    }

    #[Test]
    public function throwsWhenCreatedFromAnInvalidString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        UserId::fromString('abracadabra');
    }

    #[Test]
    public function twoIdsWithTheSameValueAreEqual(): void
    {
        $value = (string) UserId::generate();

        $a = UserId::fromString($value);
        $b = UserId::fromString($value);

        self::assertEquals((string) $a, (string) $b);
    }

    #[Test]
    public function twoGeneratedIdsAreNotEqual(): void
    {
        $a = UserId::generate();
        $b = UserId::generate();

        self::assertNotEquals((string) $a, (string) $b);
    }
}
