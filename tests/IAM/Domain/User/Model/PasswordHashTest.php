<?php

declare(strict_types=1);

namespace Twitter\Tests\IAM\Domain\User\Model;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twitter\IAM\Domain\User\Model\Exception\InvalidPasswordException;
use Twitter\IAM\Domain\User\Model\PasswordHash;

#[Group('unit')]
#[CoversClass(PasswordHash::class)]
class PasswordHashTest extends TestCase
{
    /**
     * @throws InvalidPasswordException
     */
    #[Test]
    public function fromPlainPasswordValidationPassed(): void
    {
        $password = 'Qwerty.123';
        $passwordHash = PasswordHash::fromPlainPassword($password);

        $this->assertTrue(password_verify($password, (string) $passwordHash));
    }

    #[Test]
    public function fromPlainPasswordPassedPasswordIsTooShort(): void
    {
        $this->expectException(InvalidPasswordException::class);

        PasswordHash::fromPlainPassword('qwerty1');
    }

    /**
     * @throws InvalidPasswordException
     */
    #[Test]
    public function fromHashValidationPassed(): void
    {
        $password = 'Qwerty.123';
        $passwordHash = PasswordHash::fromPlainPassword($password);
        $newPasswordHash = PasswordHash::fromHash((string) $passwordHash);

        $this->assertTrue(password_verify($password, (string) $passwordHash));
        $this->assertEquals((string) $passwordHash, (string) $newPasswordHash);
    }

    #[Test]
    public function fromHashPassedHashIsTooShort(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PasswordHash::fromHash('abracadabra');
    }

    /**
     * @throws InvalidPasswordException
     */
    #[Test]
    public function verifyPlainPassword(): void
    {
        $password = 'Qwerty.123';
        $passwordHash = PasswordHash::fromPlainPassword($password);

        $this->assertTrue($passwordHash->verify($password));
    }

    /**
     * @throws InvalidPasswordException
     */
    #[Test]
    public function verifyPlainPasswordMismatch(): void
    {
        $password = 'Qwerty.123';
        $passwordHash = PasswordHash::fromPlainPassword($password);

        $this->assertFalse($passwordHash->verify(''));
    }
}
