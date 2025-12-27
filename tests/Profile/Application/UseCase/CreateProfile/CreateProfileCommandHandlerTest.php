<?php

declare(strict_types=1);

namespace Twitter\Tests\Profile\Application\UseCase\CreateProfile;

use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twitter\Profile\Application\UseCase\CreateProfile\CreateProfileCommand;
use Twitter\Profile\Application\UseCase\CreateProfile\CreateProfileCommandHandler;
use Twitter\Profile\Domain\Profile\Model\Profile;
use Twitter\Profile\Domain\Profile\Model\ProfileRepository;

#[Group('unit')]
#[CoversMethod(CreateProfileCommandHandler::class, 'handle')]
final class CreateProfileCommandHandlerTest extends TestCase
{
    private CreateProfileCommandHandler $handler;
    private MockObject|ProfileRepository $profileRepository;

    protected function setUp(): void
    {
        $this->profileRepository = $this->createMock(ProfileRepository::class);
        $this->handler = new CreateProfileCommandHandler($this->profileRepository);
    }

    #[Test]
    public function testHandleWithValidCommand(): void
    {
        $command = new CreateProfileCommand(
            userId: '019b5f3f-d110-7908-9177-5df439942a8b',
            name: 'John Doe',
            bio: 'Software Developer'
        );

        $this->profileRepository
            ->expects(self::once())
            ->method('add')
            ->with(self::callback(function (Profile $profile) use ($command) {
                return $profile->userId() === $command->userId &&
                    $profile->name() === $command->name &&
                    $profile->bio() === $command->bio;
            }));

        $this->handler->handle($command);
    }

    #[Test]
    public function testHandleWithNullBio(): void
    {
        $command = new CreateProfileCommand(
            userId: '019b5f3f-d110-7908-9177-5df439942a8b',
            name: 'Jane Doe',
            bio: null
        );

        $this->profileRepository
            ->expects(self::once())
            ->method('add')
            ->with(self::callback(function (Profile $profile) use ($command) {
                return $profile->userId() === $command->userId &&
                    $profile->name() === $command->name &&
                    $profile->bio() === '';
            }));

        $this->handler->handle($command);
    }
}
