<?php

declare(strict_types=1);

namespace Twitter\Tests\Profile\Infrastructure\Cache\Decorator;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Twitter\Profile\Application\UseCase\UpdateProfile\UpdateProfileCommand;
use Twitter\Profile\Application\UseCase\UpdateProfile\UpdateProfileCommandHandlerInterface;
use Twitter\Profile\Application\UseCase\UpdateProfile\UpdateProfileCommandResult;
use Twitter\Profile\Domain\Profile\Exception\ProfileNotFoundException;
use Twitter\Profile\Infrastructure\Cache\Decorator\UpdateProfileCommandHandlerDecorator;

#[Group('unit')]
#[CoversClass(UpdateProfileCommandHandlerDecorator::class)]
final class UpdateProfileCommandHandlerDecoratorTest extends TestCase
{
    private UpdateProfileCommandHandlerInterface&MockObject $inner;
    private TagAwareCacheInterface&MockObject $cache;
    private UpdateProfileCommandHandlerDecorator $decorator;

    protected function setUp(): void
    {
        $this->inner = $this->createMock(UpdateProfileCommandHandlerInterface::class);
        $this->cache = $this->createMock(TagAwareCacheInterface::class);
        $this->decorator = new UpdateProfileCommandHandlerDecorator($this->inner, $this->cache);
    }

    #[Test]
    public function itDelegatesToInnerAndInvalidatesCacheOnSuccess(): void
    {
        $userId = '019b5f3f-d110-7908-9177-5df439942a8b';

        $command = new UpdateProfileCommand($userId, 'John', 'Bio');
        $expectedResult = new UpdateProfileCommandResult('John', 'Bio', new DateTimeImmutable());

        $this->inner
            ->expects(self::once())
            ->method('handle')
            ->with($command)
            ->willReturn($expectedResult);

        $this->cache
            ->expects(self::once())
            ->method('invalidateTags')
            ->with([sprintf('profile_%s', $userId)]);

        $result = $this->decorator->handle($command);

        self::assertSame($expectedResult, $result);
    }

    #[Test]
    public function itDoesNotInvalidateCacheIfInnerHandlerFails(): void
    {
        $userId = 'user-123';
        $command = new UpdateProfileCommand($userId, 'Faulty');

        $this->inner
            ->expects(self::once())
            ->method('handle')
            ->willThrowException(new ProfileNotFoundException($userId));

        $this->cache
            ->expects(self::never())
            ->method('invalidateTags');

        $this->expectException(ProfileNotFoundException::class);
        $this->expectExceptionMessage(sprintf('Profile not found for user with id "%s"', $userId));

        $this->decorator->handle($command);
    }
}
