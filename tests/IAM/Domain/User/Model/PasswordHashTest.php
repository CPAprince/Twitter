<?php

declare(strict_types=1);

namespace Twitter\Tests\IAM\Domain\User\Model;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twitter\IAM\Domain\User\Exception\InvalidPasswordException;
use Twitter\IAM\Domain\User\Model\PasswordHash;

#[Group('unit')]
#[CoversClass(PasswordHash::class)]
class PasswordHashTest extends TestCase
{
    private const string VALID_PASSWORD = 'Pswd.123';

    #[Test]
    public function hashesAndVerifiesAValidPlainPassword(): void
    {
        $passwordHash = PasswordHash::fromPlainPassword(self::VALID_PASSWORD);

        self::assertTrue(password_verify(self::VALID_PASSWORD, (string) $passwordHash));
    }

    #[Test]
    public function rejectsATooShortPlainPassword(): void
    {
        self::expectException(InvalidPasswordException::class);

        $shortPassword = substr(self::VALID_PASSWORD, 0, -1);
        PasswordHash::fromPlainPassword($shortPassword);
    }

    #[Test]
    public function createsAValueObjectFromExistingHash(): void
    {
        $passwordHash = PasswordHash::fromPlainPassword(self::VALID_PASSWORD);

        self::assertTrue(
            password_verify(
                self::VALID_PASSWORD,
                (string) $passwordHash,
            ),
        );
        self::assertEquals(
            (string) $passwordHash,
            (string) PasswordHash::fromHash((string) $passwordHash),
        );
    }

    #[Test]
    public function rejectsAnInvalidPasswordHash(): void
    {
        self::expectException(InvalidArgumentException::class);

        PasswordHash::fromHash('abracadabra');
    }

    #[Test]
    #[DataProvider('passwordVerificationProvider')]
    public function verifiesPlainPasswordsCorrectly(string $input, bool $expected): void
    {
        $passwordHash = PasswordHash::fromPlainPassword(self::VALID_PASSWORD);

        self::assertSame($expected, $passwordHash->verify($input));
    }

    public static function passwordVerificationProvider(): array
    {
        return [
            'valid password' => [self::VALID_PASSWORD, true],
            'empty password' => ['', false],
            'short password' => ['Psd.123', false],
            'password with no uppercase letter' => ['pswd.123', false],
            'password with no lowercase letter' => ['PSWD.123', false],
            'password with no number' => ['Pas.word', false],
        ];
    }
}
