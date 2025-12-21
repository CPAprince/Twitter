<?php

declare(strict_types=1);

namespace Twitter\Tests\IAM\Domain\User\Model;

use PHPUnit\Framework\TestCase;
use Twitter\IAM\Domain\User\Model\Profile;
use Twitter\IAM\Domain\User\Model\User;

class ProfileTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User('test@example.com', 'password');
    }

    public function testConstructorAndGetters(): void
    {
        $profile = new Profile($this->user, 'John Doe', 'A sample bio.');

        $this->assertSame($this->user, $profile->getUser());
        $this->assertSame('John Doe', $profile->getName());
        $this->assertSame('A sample bio.', $profile->getBio());
        $this->assertNull($profile->getUpdatedAt());
    }

    public function testChangeNameUpdatesNameAndUpdatedAt(): void
    {
        $profile = new Profile($this->user, 'Old Name');
        $this->assertNull($profile->getUpdatedAt());

        $profile->changeName('New Name');

        $this->assertSame('New Name', $profile->getName());
        $this->assertInstanceOf(\DateTimeImmutable::class, $profile->getUpdatedAt());
    }

    public function testChangeNameWithSameNameDoesNotUpdateAnything(): void
    {
        $profile = new Profile($this->user, 'John Doe');

        // Make one change to set the updatedAt timestamp
        $profile->changeName('Jane Doe');
        $initialUpdatedAt = $profile->getUpdatedAt();
        $this->assertNotNull($initialUpdatedAt);

        // Now, call the method again with the same name
        $profile->changeName('Jane Doe');

        // Assert that the timestamp has not changed
        $this->assertSame($initialUpdatedAt, $profile->getUpdatedAt());
    }

    public function testChangeBioUpdatesBioAndUpdatedAt(): void
    {
        $profile = new Profile($this->user, 'John Doe', 'Old Bio');
        $this->assertNull($profile->getUpdatedAt());

        $profile->changeBio('New Bio');

        $this->assertSame('New Bio', $profile->getBio());
        $this->assertInstanceOf(\DateTimeImmutable::class, $profile->getUpdatedAt());
    }

    public function testChangeBioWithSameBioDoesNotUpdateAnything(): void
    {
        $profile = new Profile($this->user, 'John Doe', 'Old Bio');

        // Make one change to set the updatedAt timestamp
        $profile->changeBio('New Bio');
        $initialUpdatedAt = $profile->getUpdatedAt();
        $this->assertNotNull($initialUpdatedAt);

        // Now, call the method again with the same bio
        $profile->changeBio('New Bio');

        // Assert that the timestamp has not changed
        $this->assertSame($initialUpdatedAt, $profile->getUpdatedAt());
    }
}
