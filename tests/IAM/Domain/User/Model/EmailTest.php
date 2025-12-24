<?php

declare(strict_types=1);

namespace Twitter\Tests\IAM\Domain\User\Model;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twitter\IAM\Domain\User\Exception\InvalidEmailException;
use Twitter\IAM\Domain\User\Model\Email;

#[Group('unit')]
#[CoversClass(Email::class)]
class EmailTest extends TestCase
{
    #[Test]
    public function canBeCreatedFromAValidEmailString(): void
    {
        $value = 'test@example.com';

        $email = Email::fromString($value);

        self::assertSame($value, (string) $email);
    }

    #[Test]
    public function throwsWhenCreatedFromAnInvalidEmailString(): void
    {
        $this->expectException(InvalidEmailException::class);

        Email::fromString('test@example');
    }

    #[Test]
    public function twoEmailsWithTheSameValueAreEqual(): void
    {
        $value = 'test@example.com';

        $a = Email::fromString($value);
        $b = Email::fromString($value);

        self::assertSame((string) $a, (string) $b);
    }

    #[Test]
    public function emailsWithDifferentValuesAreNotEqual(): void
    {
        $a = Email::fromString('a@example.com');
        $b = Email::fromString('b@example.com');

        self::assertNotSame((string) $a, (string) $b);
    }
}
