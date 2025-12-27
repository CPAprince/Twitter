<?php

namespace Twitter\Tests\Profile\Domain\Profile\Model;

use Assert\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twitter\Profile\Domain\Profile\Model\Profile;

#[Group('unit')]
#[CoversClass(Profile::class)]
final class ProfileTest extends TestCase
{
    /**
     * Test that a valid Profile object can be successfully created.
     */
    #[Test]
    public function createValidProfile(): void
    {
        $userId = '019b5f3f-d110-7908-9177-5df439942a8b';
        $name = 'John Doe';
        $bio = 'This is a sample bio';

        $profile = Profile::create($userId, $name, $bio);

        $this->assertSame($userId, $profile->userId());
        $this->assertSame($name, $profile->name());
        $this->assertSame($bio, $profile->bio());
        $this->assertNotNull($profile->createdAt());
        $this->assertNotNull($profile->updatedAt());
    }

    /**
     * Test that a Profile object can be created without a bio.
     */
    #[Test]
    public function createWithoutBio(): void
    {
        $userId = '019b5f3f-d110-7908-9177-5df439942a8b';
        $name = 'Jane Doe';

        $profile = Profile::create($userId, $name);

        $this->assertSame('', $profile->bio());
    }

    /**
     * Test that creating a Profile with an invalid UUID throws an exception.
     */
    #[Test]
    public function createInvalidUserId(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Profile::create('invalid-uuid', 'John Doe');
    }

    /**
     * Test that creating a Profile with a blank name throws an exception.
     */
    #[Test]
    public function createBlankName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Profile::create('019b5f3f-d110-7908-9177-5df439942a8b', '');
    }

    /**
     * Test that creating a Profile with a name shorter than 3 characters throws an exception.
     */
    #[Test]
    public function createShortName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Profile::create('019b5f3f-d110-7908-9177-5df439942a8b', 'Jo');
    }

    /**
     * Test that creating a Profile with a name longer than 60 characters throws an exception.
     */
    #[Test]
    public function createLongName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $name = str_repeat('a', 61);
        Profile::create('019b5f3f-d110-7908-9177-5df439942a8b', $name);
    }

    /**
     * Test that creating a Profile with a bio longer than 160 characters throws an exception.
     */
    #[Test]
    public function createLongBio(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $bio = str_repeat('b', 161);
        Profile::create('019b5f3f-d110-7908-9177-5df439942a8b', 'John Doe', $bio);
    }
}
