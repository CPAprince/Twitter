<?php

declare(strict_types=1);

namespace Twitter\Tests\IAM\Domain\User\Model;

use PHPUnit\Framework\TestCase;
use Twitter\IAM\Domain\User\Model\User;

class UserTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $email = 'test@example.com';
        $passwordHash = 'hashed_password';
        $roles = ['ROLE_ADMIN'];

        $user = new User($email, $passwordHash, $roles);

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame($email, $user->getEmail());
        $this->assertSame($passwordHash, $user->getPassword());
        $this->assertNull($user->getEmailVerifiedAt());
    }

    public function testGetRolesGuaranteesUserRole(): void
    {
        $user = new User('test@example.com', 'pass');
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testGetRolesIncludesAdditionalRoles(): void
    {
        $user = new User('test@example.com', 'pass', ['ROLE_CUSTOM']);
        $this->assertContains('ROLE_USER', $user->getRoles());
        $this->assertContains('ROLE_CUSTOM', $user->getRoles());
    }

    public function testGetRolesHandlesDuplicates(): void
    {
        $user = new User('test@example.com', 'pass', ['ROLE_USER', 'ROLE_ADMIN']);
        $expectedRoles = ['ROLE_USER', 'ROLE_ADMIN'];
        $this->assertCount(count($expectedRoles), $user->getRoles());
        foreach ($expectedRoles as $role) {
            $this->assertContains($role, $user->getRoles());
        }
    }

    public function testGetUserIdentifier(): void
    {
        $email = 'identifier@example.com';
        $user = new User($email, 'pass');
        $this->assertSame($email, $user->getUserIdentifier());
    }
}
