<?php

declare(strict_types=1);

namespace Twitter\Tests\IAM\Application\CreateUser;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Uid\Uuid;
use Twitter\IAM\Application\CreateUser\CreateUserCommand;
use Twitter\IAM\Application\CreateUser\CreateUserCommandHandler;
use Twitter\IAM\Domain\User\Model\Exception\InvalidEmailException;
use Twitter\IAM\Domain\User\Model\Exception\InvalidPasswordException;
use Twitter\IAM\Domain\User\Model\Exception\UserAlreadyExistsException;
use Twitter\IAM\Domain\User\Model\UserRepository;

#[Group('unit')]
#[CoversClass(CreateUserCommandHandler::class)]
class CreateUserCommandHandlerTest extends TestCase
{
    private UserRepository&MockObject $userRepository;
    private CreateUserCommandHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->handler = new CreateUserCommandHandler($this->userRepository);
    }

    protected function tearDown(): void
    {
        unset($this->userRepository, $this->handler);

        parent::tearDown();
    }

    #[Test]
    public function handleReturnsSuccessWhenUserIsCreated(): void
    {
        $this->userRepository
            ->expects(self::once())
            ->method('add');

        $result = $this->handler->handle(
            new CreateUserCommand(
                'test@example.com',
                'qwerty123',
            ),
        );

        $this->assertTrue(Uuid::isValid($result->userId));
    }

    #[Test]
    public function handleFailsWhenEmailIsInvalid(): void
    {
        $this->expectException(InvalidEmailException::class);
        $this->userRepository->expects(self::never())->method('existsByEmail');

        $this->handler->handle(
            new CreateUserCommand(
                'test@example',
                'qwerty123',
            ),
        );
    }

    #[Test]
    public function handleFailsWhenUserAlreadyExists(): void
    {
        $this->expectException(UserAlreadyExistsException::class);
        $this->userRepository->expects(self::never())->method('add');

        $expectedEmail = 'test@example.com';

        $this->userRepository
            ->expects(self::once())
            ->method('existsByEmail')
            ->willThrowException(new UserAlreadyExistsException($expectedEmail));

        $this->handler->handle(
            new CreateUserCommand(
                $expectedEmail,
                'qwerty123',
            ),
        );
    }

    #[Test]
    public function handleFailsWhenPasswordIsInvalid(): void
    {
        $this->expectException(InvalidPasswordException::class);
        $this->userRepository->expects(self::never())->method('add');

        $this->userRepository
            ->expects(self::once())
            ->method('existsByEmail')
            ->willReturn(false);

        $this->handler->handle(
            new CreateUserCommand(
                'test@example.com',
                'qwerty1',
            ),
        );
    }

    #[Test]
    public function handleSaveFailsOnUnexpectedException(): void
    {
        $this->userRepository
            ->expects(self::once())
            ->method('existsByEmail')
            ->willReturn(false);

        $this->expectException(RuntimeException::class);

        $this->userRepository
            ->expects(self::once())
            ->method('add')
            ->willThrowException(new RuntimeException());

        $this->handler->handle(
            new CreateUserCommand(
                'test@example.com',
                'qwerty123',
            ),
        );
    }
}
