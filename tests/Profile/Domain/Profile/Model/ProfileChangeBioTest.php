<?php

declare(strict_types=1);

namespace Twitter\Tests\Profile\Domain\Profile\Model;

use Assert\AssertionFailedException;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twitter\Profile\Domain\Profile\Model\Profile;

#[Group('unit')]
#[CoversMethod(Profile::class, 'changeBio')]
class ProfileChangeBioTest extends TestCase
{
    #[Test]
    public function changesBioToAnotherValidOne()
    {
        $bio = 'It is more comfortable for you to label me as insane';

        $profile = Profile::create('7d598abe-8dbb-4e4b-8bed-3553e702fda0', 'John Doe', $bio);
        $profile->changeBio('It seems that envy is my sin');

        self::assertNotSame($profile->bio(), $bio);
    }

    #[Test]
    public function changesBioToEmpty()
    {
        $profile = Profile::create(
            '7d598abe-8dbb-4e4b-8bed-3553e702fda0',
            'John Doe',
            'It is more comfortable for you to label me as insane',
        );
        $profile->changeBio('');

        self::assertEmpty($profile->bio());
    }

    #[Test]
    public function failsWhenBioIsTooLong()
    {
        $profile = Profile::create('7d598abe-8dbb-4e4b-8bed-3553e702fda0', 'John Doe');

        self::expectException(AssertionFailedException::class);

        $profile->changeBio(str_repeat('a', 301));
    }
}
