<?php

declare(strict_types=1);

namespace Twitter\Tests\Domain\User\Model;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twitter\IAM\Domain\User\Model\Email;
use Twitter\IAM\Domain\User\Model\Exception\InvalidEmailException;

#[Group('unit')]
class EmailTest extends TestCase
{
    #[Test]
    public function fromStringValidationPassed(): void
    {
        $emailString = 'test@example.com';
        $email = Email::fromString($emailString);

        $this->assertEquals($emailString, $email->toString());
    }

    #[Test]
    public function fromStringPassedInvalidEmail(): void
    {
        $this->expectException(InvalidEmailException::class);

        $email = Email::fromString('test@example');
    }
}
