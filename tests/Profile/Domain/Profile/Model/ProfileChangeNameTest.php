<?php

declare(strict_types=1);

namespace Twitter\Tests\Profile\Domain\Profile\Model;

use Assert\LazyAssertionException;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twitter\Profile\Domain\Profile\Model\Profile;

#[Group('unit')]
#[CoversMethod(Profile::class, 'changeName')]
class ProfileChangeNameTest extends TestCase
{
    #[Test]
    public function changeNameToAnotherValidOne()
    {
        $name = 'John Doe';
        $profile = Profile::create('7d598abe-8dbb-4e4b-8bed-3553e702fda0', $name);
        $profile->changeName('David Mills');

        self::assertNotSame($profile->name(), $name);
    }

    #[Test]
    #[DataProvider('invalidNameProvider')]
    public function failsWhenNameIsInvalid(string $name)
    {
        self::expectException(LazyAssertionException::class);

        $profile = Profile::create('7d598abe-8dbb-4e4b-8bed-3553e702fda0', 'John Doe');
        $profile->changeName($name);
    }

    public static function invalidNameProvider(): iterable
    {
        return [
            yield 'empty name' => [''],
            yield 'blank name' => [' '],
            yield 'less than 3 characters' => ['Jo'],
            yield 'more than 50 characters' => [str_repeat('a', 51)],
        ];
    }
}
